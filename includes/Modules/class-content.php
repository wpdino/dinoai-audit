<?php
/**
 * Content module.
 *
 * @package WPDINO\DinoAIAudit
 */

namespace WPDINO\DinoAIAudit\Modules;

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAIAudit\Loader;
use WPDINO\DinoAIAudit\Plugin;

/**
 * AI content actions for the editor.
 *
 * @since 1.0.0
 */
class Content {

	/**
	 * Plugin instance.
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Plugin $plugin Plugin instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Registers hooks.
	 *
	 * @since 1.0.0
	 *
	 * @param Loader $loader Hook loader.
	 * @return void
	 */
	public function register( Loader $loader ) {
		$loader->add_action( 'add_meta_boxes', $this, 'register_classic_metabox' );
	}

	/**
	 * Adds a classic editor fallback metabox.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_classic_metabox() {
		$screen = get_current_screen();

		if ( ! $screen || 'post' !== $screen->base ) {
			return;
		}

		if ( function_exists( 'use_block_editor_for_post_type' ) && use_block_editor_for_post_type( $screen->post_type ) ) {
			return;
		}

		add_meta_box(
			'dinoai-audit-content',
			__( 'DinoAI Audit', 'dinoai-audit' ),
			array( $this, 'render_classic_metabox' ),
			null,
			'side',
			'default'
		);
	}

	/**
	 * Renders the classic editor metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $post Post.
	 * @return void
	 */
	public function render_classic_metabox( $post ) {
		unset( $post );
		include DINOAI_AUDIT_PATH . 'admin/views/classic-metabox.php';
	}

	/**
	 * Available content actions.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, string>
	 */
	public static function actions() {
		return array(
			'content.improve'   => __( 'Improve', 'dinoai-audit' ),
			'content.rewrite'   => __( 'Rewrite', 'dinoai-audit' ),
			'content.shorten'   => __( 'Shorten', 'dinoai-audit' ),
			'content.expand'    => __( 'Expand', 'dinoai-audit' ),
			'content.summarize' => __( 'Summarize', 'dinoai-audit' ),
		);
	}
}
