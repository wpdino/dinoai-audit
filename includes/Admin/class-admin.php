<?php
/**
 * Admin assets and helpers.
 *
 * @package WPDINO\DinoAIAudit
 */

namespace WPDINO\DinoAIAudit\Admin;

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAIAudit\Loader;
use WPDINO\DinoAIAudit\Plugin;
use WPDINO\DinoAIAudit\Modules\Site_Audit;

/**
 * Enqueues admin and editor assets.
 *
 * @since 1.0.0
 */
class Admin {

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
		$loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_admin' );
		$loader->add_action( 'enqueue_block_editor_assets', $this, 'enqueue_editor' );
		$loader->add_filter( 'plugin_action_links_' . DINOAI_AUDIT_BASENAME, $this, 'action_links' );
	}

	/**
	 * Enqueues assets on DinoAI Audit admin screens and classic editor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook Current admin page.
	 * @return void
	 */
	public function enqueue_admin( $hook ) {
		$dinoai_audit_page = ( false !== strpos( $hook, 'dinoai-audit' ) );

		if ( $dinoai_audit_page ) {
			wp_enqueue_style(
				'dinoai-audit-admin',
				DINOAI_AUDIT_URL . 'admin/css/admin.css',
				array( 'dashicons' ),
				DINOAI_AUDIT_VERSION
			);

			wp_enqueue_style(
				'dinoai-audit-admin-content',
				DINOAI_AUDIT_URL . 'admin/css/dinoai-audit-content.css',
				array( 'dinoai-audit-admin' ),
				DINOAI_AUDIT_VERSION
			);

			wp_enqueue_script(
				'dinoai-audit-admin',
				DINOAI_AUDIT_URL . 'admin/js/admin.js',
				array( 'jquery', 'wp-i18n', 'wp-api-fetch' ),
				DINOAI_AUDIT_VERSION,
				true
			);

			wp_set_script_translations( 'dinoai-audit-admin', 'dinoai-audit', DINOAI_AUDIT_PATH . 'languages' );

			wp_add_inline_script(
				'dinoai-audit-admin',
				'window.dinoaiAuditAdmin = ' . wp_json_encode( $this->js_config() ) . ';',
				'before'
			);
		}

		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			wp_enqueue_style(
				'dinoai-audit-admin',
				DINOAI_AUDIT_URL . 'admin/css/admin.css',
				array( 'dashicons' ),
				DINOAI_AUDIT_VERSION
			);

			wp_enqueue_style(
				'dinoai-audit-admin-content',
				DINOAI_AUDIT_URL . 'admin/css/dinoai-audit-content.css',
				array( 'dinoai-audit-admin' ),
				DINOAI_AUDIT_VERSION
			);

			wp_enqueue_script(
				'dinoai-audit-classic',
				DINOAI_AUDIT_URL . 'admin/js/classic.js',
				array( 'jquery', 'wp-i18n', 'wp-api-fetch' ),
				DINOAI_AUDIT_VERSION,
				true
			);

			wp_set_script_translations( 'dinoai-audit-classic', 'dinoai-audit', DINOAI_AUDIT_PATH . 'languages' );

			wp_add_inline_script(
				'dinoai-audit-classic',
				'window.dinoaiAuditAdmin = ' . wp_json_encode( $this->js_config() ) . ';',
				'before'
			);
		}
	}

	/**
	 * Enqueues Gutenberg assets.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_editor() {
		$asset_file = DINOAI_AUDIT_PATH . 'build/index.asset.php';
		$asset      = is_readable( $asset_file ) ? require $asset_file : array(
			'dependencies' => array(
				'wp-plugins',
				'wp-edit-post',
				'wp-editor',
				'wp-element',
				'wp-components',
				'wp-data',
				'wp-i18n',
				'wp-api-fetch',
				'wp-block-editor',
				'wp-blocks',
				'wp-core-data',
				'wp-rich-text',
			),
			'version'      => DINOAI_AUDIT_VERSION,
		);

		wp_enqueue_style(
			'dinoai-audit-editor',
			DINOAI_AUDIT_URL . 'admin/css/editor.css',
			array( 'wp-components' ),
			DINOAI_AUDIT_VERSION
		);

		wp_enqueue_script(
			'dinoai-audit-editor',
			DINOAI_AUDIT_URL . 'build/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_set_script_translations( 'dinoai-audit-editor', 'dinoai-audit', DINOAI_AUDIT_PATH . 'languages' );

		wp_add_inline_script(
			'dinoai-audit-editor',
			'window.dinoaiAuditEditor = ' . wp_json_encode( $this->js_config() ) . ';',
			'before'
		);
	}

	/**
	 * Plugin row links.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $links Existing links.
	 * @return string[]
	 */
	public function action_links( $links ) {
		$settings = '<a href="' . esc_url( admin_url( 'admin.php?page=dinoai-audit-settings' ) ) . '">' . esc_html__( 'Settings', 'dinoai-audit' ) . '</a>';
		array_unshift( $links, $settings );
		return $links;
	}

	/**
	 * Shared JS config. Never includes the API key.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>
	 */
	protected function js_config() {
		$post_id = 0;

		if ( function_exists( 'get_the_ID' ) ) {
			$post_id = (int) get_the_ID();
		}

		$audit_screen = Site_Audit::screen();

		$config = array(
			'restUrl'        => rest_url( 'dinoai-audit/v1/' ),
			'settingsUrl'    => admin_url( 'admin.php?page=dinoai-audit-settings' ),
			'configured'     => $this->plugin->settings()->is_configured(),
			'modules'        => array(
				'content' => $this->plugin->settings()->is_module_enabled( 'content' ),
				'seo'     => $this->plugin->settings()->is_module_enabled( 'seo' ),
				'images'  => $this->plugin->settings()->is_module_enabled( 'images' ),
				'audit'   => $this->plugin->settings()->is_module_enabled( 'audit' ),
			),
			'supportsVision' => $this->plugin->ai()->supports_vision(),
			'postId'         => $post_id,
			'audit'          => array(
				'running' => $audit_screen['running'],
				'empty'   => $audit_screen['empty'],
			),
		);

		/**
		 * Filters editor/admin JS config. Never include API keys.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, mixed> $config Config.
		 */
		return apply_filters( 'dinoai_audit_js_config', $config );
	}
}
