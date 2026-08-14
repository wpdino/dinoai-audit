/**
 * DinoAI Gutenberg sidebar.
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
} from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { createElement as el, useState, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { createBlock } from '@wordpress/blocks';
import { create, insert, toHTMLString } from '@wordpress/rich-text';
import { SVG, Path } from '@wordpress/primitives';

const config = window.dinoaiEditor || {};

function DinoAIIcon() {
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
		'DinoAI could not complete the request. Please check your AI provider settings and try again.',
		'dinoai'
	);
}

function getSelectedPlainText() {
	const selection = window.getSelection();
	return selection ? selection.toString() : '';
}

function DinoAISidebar() {
	const [ status, setStatus ] = useState( { type: '', message: '' } );
	const [ loading, setLoading ] = useState( false );
	const [ result, setResult ] = useState( '' );
	const [ structured, setStructured ] = useState( null );

	const { insertBlocks, updateBlockAttributes, insertBlock } =
		useDispatch( 'core/block-editor' );
	const { editPost } = useDispatch( 'core/editor' );

	const editorState = useSelect( ( select ) => {
		const blockEditor = select( 'core/block-editor' );
		const editor = select( 'core/editor' );
		const selected = blockEditor.getSelectedBlock();
		const start = blockEditor.getSelectionStart();
		const end = blockEditor.getSelectionEnd();

		return {
			postId: editor.getCurrentPostId(),
			title: editor.getEditedPostAttribute( 'title' ) || '',
			selected,
			start,
			end,
			selectedClientId: selected ? selected.clientId : null,
		};
	}, [] );

	function request( path, data ) {
		setLoading( true );
		setStatus( { type: '', message: '' } );

		return apiFetch( {
			path: '/dinoai/v1' + path,
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
		const input = getSelectedPlainText();

		if ( ! input ) {
			setStatus( {
				type: 'error',
				message: __( 'Select some text in the editor first.', 'dinoai' ),
			} );
			return;
		}

		request( '/generate', {
			prompt_key: promptKey,
			input,
			post_id: editorState.postId,
		} ).then( ( response ) => {
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
			message: __( 'Copied.', 'dinoai' ),
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
			message: __( 'Inserted as a new paragraph. Review it before publishing.', 'dinoai' ),
		} );
	}

	function replaceSelection() {
		if ( ! result || ! editorState.selected ) {
			insertResult();
			return;
		}

		const block = editorState.selected;
		const start = editorState.start;
		const end = editorState.end;

		if (
			typeof block.attributes.content === 'string' &&
			start &&
			end &&
			start.clientId === end.clientId &&
			typeof start.offset === 'number' &&
			typeof end.offset === 'number'
		) {
			const value = create( { html: block.attributes.content } );
			const next = insert( value, result, start.offset, end.offset );
			updateBlockAttributes( block.clientId, {
				content: toHTMLString( { value: next } ),
			} );
			setStatus( {
				type: 'success',
				message: __( 'Selected text replaced. Review the change before publishing.', 'dinoai' ),
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
				message: __( 'Post title updated. Review it before publishing.', 'dinoai' ),
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
				message: __( 'Select an image block first.', 'dinoai' ),
			} );
			return;
		}

		if ( ! config.supportsVision ) {
			setStatus( {
				type: 'error',
				message: __(
					'The selected AI model cannot analyze images. Choose a vision-capable model such as gpt-4o-mini in DinoAI settings.',
					'dinoai'
				),
			} );
			return;
		}

		request( '/image-alt', { attachment_id: attachmentId } ).then(
			( response ) => {
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
			setStatus( {
				type: 'success',
				message: __( 'Alt text saved.', 'dinoai' ),
			} );
		} );
	}

	if ( ! config.configured ) {
		return el(
			'div',
			{ className: 'dinoai-sidebar' },
			el(
				Notice,
				{ status: 'warning', isDismissible: false },
				__( 'No AI provider configured.', 'dinoai' ),
				' ',
				el(
					'a',
					{ href: ( config.settingsUrl || '' ) + '#provider' },
					__( 'Connect an AI provider in DinoAI → Settings.', 'dinoai' )
				)
			)
		);
	}

	const contentActions = [
		[ 'content.improve', __( 'Improve', 'dinoai' ) ],
		[ 'content.rewrite', __( 'Rewrite', 'dinoai' ) ],
		[ 'content.shorten', __( 'Shorten', 'dinoai' ) ],
		[ 'content.expand', __( 'Expand', 'dinoai' ) ],
		[ 'content.summarize', __( 'Summarize', 'dinoai' ) ],
	];

	return el(
		'div',
		{ className: 'dinoai-sidebar' },
		config.modules && config.modules.content
			? el(
					PanelBody,
					{ title: __( 'Content', 'dinoai' ), initialOpen: true },
					el(
						'p',
						{ className: 'dinoai-empty-state' },
						__( 'Select text in the editor, then choose an action. DinoAI will not replace content until you confirm.', 'dinoai' )
					),
					el(
						'div',
						{ className: 'dinoai-action-row' },
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
					{ title: __( 'SEO', 'dinoai' ), initialOpen: true },
					el(
						'p',
						{ className: 'dinoai-empty-state' },
						__( 'Generate suggestions from the current post. DinoAI complements dedicated SEO plugins.', 'dinoai' )
					),
					el(
						'div',
						{ className: 'dinoai-action-row' },
						el(
							Button,
							{
								variant: 'secondary',
								disabled: loading,
								onClick: () => runSeo( 'seo.title' ),
							},
							__( 'Generate title', 'dinoai' )
						),
						el(
							Button,
							{
								variant: 'secondary',
								disabled: loading,
								onClick: () => runSeo( 'seo.meta_description' ),
							},
							__( 'Generate description', 'dinoai' )
						),
						el(
							Button,
							{
								variant: 'secondary',
								disabled: loading,
								onClick: () => runSeo( 'seo.keywords' ),
							},
							__( 'Focus keywords', 'dinoai' )
						),
						el(
							Button,
							{
								variant: 'secondary',
								disabled: loading,
								onClick: () => runSeo( 'seo.analyze' ),
							},
							__( 'SEO suggestions', 'dinoai' )
						)
					),
					structured && structured.keywords
						? el(
								'div',
								{ className: 'dinoai-keyword-list' },
								structured.keywords.map( ( keyword ) =>
									el( 'span', { className: 'dinoai-keyword', key: keyword }, keyword )
								)
						  )
						: null,
					structured && structured.suggestions
						? el(
								'ul',
								{ className: 'dinoai-suggestions' },
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
								__( 'Use as post title', 'dinoai' )
						  )
						: null
			  )
			: null,
		config.modules && config.modules.images
			? el(
					PanelBody,
					{ title: __( 'Image', 'dinoai' ), initialOpen: false },
					el(
						'div',
						{ className: 'dinoai-action-row' },
						el(
							Button,
							{
								variant: 'secondary',
								disabled: loading,
								onClick: generateImageAlt,
							},
							__( 'Generate alt text', 'dinoai' )
						),
						result && structured && structured.attachment_id
							? el(
									Button,
									{
										variant: 'primary',
										disabled: loading,
										onClick: saveAlt,
									},
									__( 'Save alt text', 'dinoai' )
							  )
							: null
					)
			  )
			: null,
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
			label: __( 'Result', 'dinoai' ),
			value: result,
			onChange: setResult,
			help: __( 'Review the result, then copy, insert, or replace selected text.', 'dinoai' ),
		} ),
		el(
			'div',
			{ className: 'dinoai-result-actions' },
			el(
				Button,
				{ variant: 'secondary', disabled: ! result, onClick: copyResult },
				__( 'Copy', 'dinoai' )
			),
			el(
				Button,
				{ variant: 'secondary', disabled: ! result, onClick: insertResult },
				__( 'Insert', 'dinoai' )
			),
			el(
				Button,
				{ variant: 'primary', disabled: ! result, onClick: replaceSelection },
				__( 'Replace selection', 'dinoai' )
			)
		)
	);
}

function DinoAIPlugin() {
	return el(
		Fragment,
		null,
		el(
			PluginSidebarMoreMenuItem,
			{ target: 'dinoai-sidebar' },
			__( 'DinoAI', 'dinoai' )
		),
		el(
			PluginSidebar,
			{
				name: 'dinoai-sidebar',
				title: __( 'DinoAI', 'dinoai' ),
				icon: DinoAIIcon,
			},
			el( DinoAISidebar )
		)
	);
}

registerPlugin( 'dinoai', { render: DinoAIPlugin, icon: DinoAIIcon } );
