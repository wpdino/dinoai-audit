/**
 * DinoAI Audit Gutenberg sidebar.
 */
import { registerPlugin } from '@wordpress/plugins';
import {
	PluginSidebar,
	PluginSidebarMoreMenuItem,
} from '@wordpress/edit-post';
import {
	PanelBody,
	Button,
	TextareaControl,
	Notice,
	Spinner,
	createSlotFill,
} from '@wordpress/components';
import { useSelect, useDispatch, select as dataSelect } from '@wordpress/data';
import { createElement as el, useState, useEffect, useRef, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { createBlock } from '@wordpress/blocks';
import { create, insert, slice, getTextContent, toHTMLString } from '@wordpress/rich-text';
import { SVG, Path } from '@wordpress/primitives';

const config = window.dinoaiAuditEditor || {};
const { Slot: DinoAIAuditSidebarSlot } = createSlotFill( 'DinoAIAudit.SidebarPanels' );

function DinoAIAuditIcon() {
	return el(
		SVG,
		{ viewBox: '0 0 24 24', xmlns: 'http://www.w3.org/2000/svg' },
		el( Path, {
			d: 'M11 3.2l1.35 6.15 6.15 1.35-6.15 1.35L11 18.2l-1.35-6.15L3.5 10.7l6.15-1.35L11 3.2zm7.7 1.1.55 2.15 2.15.55-2.15.55-.55 2.15-.55-2.15-2.15-.55 2.15-.55.55-2.15z',
		} )
	);
}

function friendlyError( error ) {
	if ( error && error.message ) {
		return error.message;
	}
	return __(
		'DinoAI Audit could not complete the request. Please check your AI provider settings and try again.',
		'dinoai-audit'
	);
}

function getBlockPlainText( block ) {
	if ( ! block ) {
		return '';
	}

	if ( typeof block.attributes?.content === 'string' && block.attributes.content ) {
		return getTextContent( create( { html: block.attributes.content } ) );
	}

	if ( Array.isArray( block.innerBlocks ) && block.innerBlocks.length ) {
		return block.innerBlocks
			.map( getBlockPlainText )
			.filter( Boolean )
			.join( '\n\n' );
	}

	return '';
}

function getDomSelectedText() {
	const iframe = document.querySelector(
		'iframe[name="editor-canvas"], iframe.editor-canvas__iframe'
	);
	const win = iframe && iframe.contentWindow ? iframe.contentWindow : window;

	try {
		const selection = win.getSelection();
		return selection ? selection.toString() : '';
	} catch ( error ) {
		return '';
	}
}

function getSelectedEditorText( blockEditor ) {
	const multi = blockEditor.getMultiSelectedBlocks();

	if ( multi && multi.length ) {
		return multi.map( getBlockPlainText ).filter( Boolean ).join( '\n\n' );
	}

	const selected = blockEditor.getSelectedBlock();
	const start = blockEditor.getSelectionStart();
	const end = blockEditor.getSelectionEnd();

	if (
		selected &&
		start &&
		end &&
		start.clientId === selected.clientId &&
		end.clientId === selected.clientId &&
		typeof start.offset === 'number' &&
		typeof end.offset === 'number' &&
		start.offset !== end.offset &&
		typeof selected.attributes?.content === 'string'
	) {
		const from = Math.min( start.offset, end.offset );
		const to = Math.max( start.offset, end.offset );
		const value = create( { html: selected.attributes.content } );

		return getTextContent( slice( value, from, to ) );
	}

	if ( selected ) {
		const blockText = getBlockPlainText( selected );

		if ( blockText ) {
			return blockText;
		}
	}

	return getDomSelectedText();
}

function toRichHtml( markup ) {
	const trimmed = ( markup || '' ).trim();

	if ( ! trimmed ) {
		return '';
	}

	const single = trimmed.match( /^<p(?:\s[^>]*)?>([\s\S]*)<\/p>$/i );

	if ( single && ! /<p[\s>]/i.test( single[ 1 ] ) ) {
		return single[ 1 ];
	}

	return trimmed.replace( /^<p(?:\s[^>]*)?>/i, '' ).replace( /<\/p>$/i, '' );
}

function getSelectionSnapshot( blockEditor ) {
	const multi = blockEditor.getMultiSelectedBlocks() || [];
	const selected = blockEditor.getSelectedBlock();
	const start = blockEditor.getSelectionStart();
	const end = blockEditor.getSelectionEnd();
	const blocks = multi.length ? multi : selected ? [ selected ] : [];
	let range = null;

	if (
		selected &&
		start &&
		end &&
		start.clientId === selected.clientId &&
		end.clientId === selected.clientId &&
		typeof start.offset === 'number' &&
		typeof end.offset === 'number' &&
		start.offset !== end.offset
	) {
		range = {
			clientId: selected.clientId,
			from: Math.min( start.offset, end.offset ),
			to: Math.max( start.offset, end.offset ),
		};
	}

	return {
		selected,
		blocks,
		clientIds: blocks.map( ( block ) => block.clientId ),
		range,
		selectedText: getSelectedEditorText( blockEditor ),
	};
}

function snapshotIsUseful( snapshot ) {
	return Boolean( snapshot && ( snapshot.range || snapshot.clientIds.length ) );
}

function DinoAIAuditSidebar() {
	const [ status, setStatus ] = useState( { type: '', message: '' } );
	const [ loading, setLoading ] = useState( false );
	const [ result, setResult ] = useState( '' );
	const [ structured, setStructured ] = useState( null );
	const [ altSaved, setAltSaved ] = useState( false );

	const { insertBlocks, updateBlockAttributes, insertBlock, replaceBlocks } =
		useDispatch( 'core/block-editor' );
	const { editPost } = useDispatch( 'core/editor' );

	const lastSelectedText = useRef( '' );
	const lastSelection = useRef( null );
	const resultSelection = useRef( null );

	const editorState = useSelect( ( select ) => {
		const blockEditor = select( 'core/block-editor' );
		const editor = select( 'core/editor' );
		const snapshot = getSelectionSnapshot( blockEditor );

		return {
			postId: editor.getCurrentPostId(),
			title: editor.getEditedPostAttribute( 'title' ) || '',
			selected: snapshot.selected,
			start: blockEditor.getSelectionStart(),
			end: blockEditor.getSelectionEnd(),
			selectedClientId: snapshot.selected ? snapshot.selected.clientId : null,
			selectedText: snapshot.selectedText,
			selectionSnapshot: snapshot,
		};
	}, [] );

	useEffect( () => {
		if ( editorState.selectedText ) {
			lastSelectedText.current = editorState.selectedText;
		}

		const next = editorState.selectionSnapshot;

		if ( ! snapshotIsUseful( next ) ) {
			return;
		}

		const prev = lastSelection.current;

		if (
			prev &&
			prev.range &&
			! next.range &&
			prev.clientIds.length === 1 &&
			next.clientIds.length === 1 &&
			prev.clientIds[ 0 ] === next.clientIds[ 0 ]
		) {
			return;
		}

		lastSelection.current = next;
	}, [ editorState.selectedText, editorState.selectionSnapshot ] );

	function request( path, data ) {
		setLoading( true );
		setStatus( { type: '', message: '' } );

		return apiFetch( {
			path: '/dinoai-audit/v1' + path,
			method: 'POST',
			data,
		} )
			.catch( ( error ) => {
				setStatus( { type: 'error', message: friendlyError( error ) } );
				throw error;
			} )
			.finally( () => {
				setLoading( false );
			} );
	}

	function runContent( promptKey ) {
		const input = ( editorState.selectedText || lastSelectedText.current || '' ).trim();

		if ( ! input ) {
			setStatus( {
				type: 'error',
				message: __( 'Select some text in the editor first.', 'dinoai-audit' ),
			} );
			return;
		}

		const snapshot = lastSelection.current;

		request( '/generate', {
			prompt_key: promptKey,
			input,
			post_id: editorState.postId,
		} ).then( ( response ) => {
			resultSelection.current = snapshot;
			setResult( response.text || '' );
			setStructured( response.structured || null );
		} );
	}

	function runSeo( promptKey ) {
		request( '/generate', {
			prompt_key: promptKey,
			input: '',
			post_id: editorState.postId,
		} ).then( ( response ) => {
			setResult( response.text || '' );
			setStructured( response.structured || null );
		} );
	}

	function copyResult() {
		if ( ! result ) {
			return;
		}
		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( result );
		}
		setStatus( {
			type: 'success',
			message: __( 'Copied.', 'dinoai-audit' ),
		} );
	}

	function insertResult() {
		if ( ! result ) {
			return;
		}

		const block = createBlock( 'core/paragraph', { content: result } );

		if ( editorState.selectedClientId ) {
			insertBlock( block, undefined, undefined, false );
		} else {
			insertBlocks( [ block ] );
		}

		setStatus( {
			type: 'success',
			message: __( 'Inserted as a new paragraph. Review it before publishing.', 'dinoai-audit' ),
		} );
	}

	function replaceSelection() {
		if ( ! result ) {
			return;
		}

		const snapshot = resultSelection.current || lastSelection.current;
		const html = toRichHtml( result );
		const range = snapshot && snapshot.range ? snapshot.range : null;
		const clientIds = snapshot && snapshot.clientIds ? snapshot.clientIds : [];
		const rangeBlock = range
			? dataSelect( 'core/block-editor' ).getBlock( range.clientId ) ||
			  ( snapshot && snapshot.selected )
			: null;

		if (
			range &&
			rangeBlock &&
			typeof rangeBlock.attributes?.content === 'string'
		) {
			const value = create( { html: rangeBlock.attributes.content } );
			const next = insert( value, create( { html } ), range.from, range.to );

			updateBlockAttributes( rangeBlock.clientId, {
				content: toHTMLString( { value: next } ),
			} );
			setStatus( {
				type: 'success',
				message: __( 'Selected text replaced. Review the change before publishing.', 'dinoai-audit' ),
			} );
			return;
		}

		if ( clientIds.length === 1 ) {
			const block =
				dataSelect( 'core/block-editor' ).getBlock( clientIds[ 0 ] ) ||
				( snapshot && snapshot.blocks && snapshot.blocks[ 0 ] );

			if ( block && typeof block.attributes?.content === 'string' ) {
				updateBlockAttributes( clientIds[ 0 ], { content: html } );
				setStatus( {
					type: 'success',
					message: __( 'Selected text replaced. Review the change before publishing.', 'dinoai-audit' ),
				} );
				return;
			}
		}

		if ( clientIds.length ) {
			replaceBlocks( clientIds, [
				createBlock( 'core/paragraph', { content: html } ),
			] );
			setStatus( {
				type: 'success',
				message: __( 'Selected text replaced. Review the change before publishing.', 'dinoai-audit' ),
			} );
			return;
		}

		insertResult();
	}

	function applyTitle() {
		if ( structured && structured.title ) {
			editPost( { title: structured.title } );
			setStatus( {
				type: 'success',
				message: __( 'Post title updated. Review it before publishing.', 'dinoai-audit' ),
			} );
		}
	}

	function generateImageAlt() {
		const block = editorState.selected;
		const attachmentId =
			block && block.name === 'core/image'
				? parseInt( block.attributes.id, 10 )
				: 0;

		if ( ! attachmentId ) {
			setStatus( {
				type: 'error',
				message: __( 'Select an image block first.', 'dinoai-audit' ),
			} );
			return;
		}

		if ( ! config.supportsVision ) {
			setStatus( {
				type: 'error',
				message: __(
					'The selected AI model cannot analyze images. Choose a vision-capable model such as gpt-4o-mini in DinoAI Audit settings.',
					'dinoai-audit'
				),
			} );
			return;
		}

		request( '/image-alt', { attachment_id: attachmentId } ).then(
			( response ) => {
				setAltSaved( false );
				setResult( response.alt_text || '' );
				setStructured( { alt_text: response.alt_text, attachment_id: attachmentId } );
			}
		);
	}

	function saveAlt() {
		const attachmentId =
			structured && structured.attachment_id
				? structured.attachment_id
				: editorState.selected && editorState.selected.attributes
				? parseInt( editorState.selected.attributes.id, 10 )
				: 0;

		if ( ! attachmentId || ! result ) {
			return;
		}

		request( '/image-alt/save', {
			attachment_id: attachmentId,
			alt_text: result,
		} ).then( ( response ) => {
			if ( editorState.selected && editorState.selected.name === 'core/image' ) {
				updateBlockAttributes( editorState.selected.clientId, {
					alt: response.alt_text,
				} );
			}
			setAltSaved( true );
			setStatus( {
				type: 'success',
				message: __(
					'Alt text saved. Save or publish this post or page to keep the change.',
					'dinoai-audit'
				),
			} );
		} );
	}

	if ( ! config.configured ) {
		return el(
			'div',
			{ className: 'dinoai-audit-sidebar' },
			el(
				Notice,
				{ status: 'warning', isDismissible: false },
				__( 'No AI provider configured.', 'dinoai-audit' ),
				' ',
				el(
					'a',
					{ href: ( config.settingsUrl || '' ) + '#provider' },
					__( 'Connect an AI provider in DinoAI Audit → Settings.', 'dinoai-audit' )
				)
			)
		);
	}

	const contentActions = [
		[ 'content.improve', __( 'Improve', 'dinoai-audit' ) ],
		[ 'content.rewrite', __( 'Rewrite', 'dinoai-audit' ) ],
		[ 'content.shorten', __( 'Shorten', 'dinoai-audit' ) ],
		[ 'content.expand', __( 'Expand', 'dinoai-audit' ) ],
		[ 'content.summarize', __( 'Summarize', 'dinoai-audit' ) ],
	];

	return el(
		'div',
		{ className: 'dinoai-audit-sidebar' },
		config.modules && config.modules.content
			? el(
					PanelBody,
					{ title: __( 'Content', 'dinoai-audit' ), initialOpen: true },
					el(
						'p',
						{ className: 'dinoai-audit-empty-state' },
						__( 'Select text in the editor, then choose an action. DinoAI Audit will not replace content until you confirm.', 'dinoai-audit' )
					),
					el(
						'div',
						{ className: 'dinoai-audit-action-row' },
						contentActions.map( ( item ) =>
							el(
								Button,
								{
									key: item[ 0 ],
									variant: 'secondary',
									disabled: loading,
									onClick: () => runContent( item[ 0 ] ),
								},
								item[ 1 ]
							)
						)
					)
			  )
			: null,
		config.modules && config.modules.seo
			? el(
					PanelBody,
					{ title: __( 'SEO', 'dinoai-audit' ), initialOpen: true },
					el(
						'p',
						{ className: 'dinoai-audit-empty-state' },
						__( 'Generate suggestions from the current post. DinoAI Audit complements dedicated SEO plugins.', 'dinoai-audit' )
					),
					el(
						'div',
						{ className: 'dinoai-audit-action-row' },
						el(
							Button,
							{
								variant: 'secondary',
								disabled: loading,
								onClick: () => runSeo( 'seo.title' ),
							},
							__( 'Generate title', 'dinoai-audit' )
						),
						el(
							Button,
							{
								variant: 'secondary',
								disabled: loading,
								onClick: () => runSeo( 'seo.meta_description' ),
							},
							__( 'Generate description', 'dinoai-audit' )
						),
						el(
							Button,
							{
								variant: 'secondary',
								disabled: loading,
								onClick: () => runSeo( 'seo.keywords' ),
							},
							__( 'Focus keywords', 'dinoai-audit' )
						),
						el(
							Button,
							{
								variant: 'secondary',
								disabled: loading,
								onClick: () => runSeo( 'seo.analyze' ),
							},
							__( 'SEO suggestions', 'dinoai-audit' )
						)
					),
					structured && structured.keywords
						? el(
								'div',
								{ className: 'dinoai-audit-keyword-list' },
								structured.keywords.map( ( keyword ) =>
									el( 'span', { className: 'dinoai-audit-keyword', key: keyword }, keyword )
								)
						  )
						: null,
					structured && structured.suggestions
						? el(
								'ul',
								{ className: 'dinoai-audit-suggestions' },
								structured.suggestions.map( ( item, index ) =>
									el( 'li', { key: index }, item.message )
								)
						  )
						: null,
					structured && structured.title
						? el(
								Button,
								{
									variant: 'secondary',
									onClick: applyTitle,
									style: { marginTop: '8px' },
								},
								__( 'Use as post title', 'dinoai-audit' )
						  )
						: null
			  )
			: null,
		config.modules && config.modules.images
			? el(
					PanelBody,
					{ title: __( 'Image', 'dinoai-audit' ), initialOpen: false },
					el(
						'div',
						{ className: 'dinoai-audit-action-row' },
						el(
							Button,
							{
								variant: 'secondary',
								disabled: loading,
								onClick: generateImageAlt,
							},
							__( 'Generate alt text', 'dinoai-audit' )
						),
						result && structured && structured.attachment_id
							? el(
									Button,
									{
										variant: 'primary',
										disabled: loading || altSaved,
										onClick: saveAlt,
									},
									__( 'Save alt text', 'dinoai-audit' )
							  )
							: null
					)
			  )
			: null,
		el( DinoAIAuditSidebarSlot, { bubblesVirtually: true } ),
		loading ? el( Spinner ) : null,
		status.message
			? el(
					Notice,
					{
						status: status.type === 'error' ? 'error' : 'success',
						isDismissible: false,
					},
					status.message
			  )
			: null,
		el( TextareaControl, {
			label: __( 'Result', 'dinoai-audit' ),
			value: result,
			onChange: ( value ) => {
				setResult( value );
				if ( altSaved ) {
					setAltSaved( false );
				}
			},
			help: __( 'Review the result, then copy, insert, or replace selected text.', 'dinoai-audit' ),
		} ),
		el(
			'div',
			{ className: 'dinoai-audit-result-actions' },
			el(
				Button,
				{ variant: 'secondary', disabled: ! result, onClick: copyResult },
				__( 'Copy', 'dinoai-audit' )
			),
			el(
				Button,
				{ variant: 'secondary', disabled: ! result, onClick: insertResult },
				__( 'Insert', 'dinoai-audit' )
			),
			el(
				Button,
				{ variant: 'primary', disabled: ! result, onClick: replaceSelection },
				__( 'Replace selection', 'dinoai-audit' )
			)
		)
	);
}

function DinoAIAuditPlugin() {
	return el(
		Fragment,
		null,
		el(
			PluginSidebarMoreMenuItem,
			{ target: 'dinoai-audit-sidebar' },
			__( 'DinoAI Audit', 'dinoai-audit' )
		),
		el(
			PluginSidebar,
			{
				name: 'dinoai-audit-sidebar',
				title: __( 'DinoAI Audit', 'dinoai-audit' ),
				icon: DinoAIAuditIcon,
			},
			el( DinoAIAuditSidebar )
		)
	);
}

registerPlugin( 'dinoai-audit', { render: DinoAIAuditPlugin, icon: DinoAIAuditIcon } );
