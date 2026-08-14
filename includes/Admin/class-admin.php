<?php
/**
 * Admin assets and helpers.
 *
 * @package WPDINO\DinoAI
 */

namespace WPDINO\DinoAI\Admin;

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAI\Loader;
use WPDINO\DinoAI\Plugin;

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
		$loader->add_filter( 'plugin_action_links_' . DINOAI_BASENAME, $this, 'action_links' );
	}

	/**
	 * Enqueues assets on DinoAI admin screens and classic editor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook Current admin page.
	 * @return void
	 */
	public function enqueue_admin( $hook ) {
		$dinoai_page = ( false !== strpos( $hook, 'dinoai' ) );

		if ( $dinoai_page ) {
			wp_enqueue_style(
				'dinoai-admin',
				DINOAI_URL . 'admin/css/admin.css',
				array( 'dashicons' ),
				DINOAI_VERSION
			);

			wp_enqueue_style(
				'dinoai-admin-content',
				DINOAI_URL . 'admin/css/dinoai-content.css',
				array( 'dinoai-admin' ),
				DINOAI_VERSION
			);

			wp_enqueue_script(
				'dinoai-admin',
				DINOAI_URL . 'admin/js/admin.js',
				array( 'jquery', 'wp-i18n', 'wp-api-fetch' ),
				DINOAI_VERSION,
				true
			);

			wp_set_script_translations( 'dinoai-admin', 'dinoai', DINOAI_PATH . 'languages' );

			wp_add_inline_script(
				'dinoai-admin',
				'window.dinoaiAdmin = ' . wp_json_encode( $this->js_config() ) . ';',
				'before'
			);
		}

		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			wp_enqueue_style(
				'dinoai-admin',
				DINOAI_URL . 'admin/css/admin.css',
				array( 'dashicons' ),
				DINOAI_VERSION
			);

			wp_enqueue_style(
				'dinoai-admin-content',
				DINOAI_URL . 'admin/css/dinoai-content.css',
				array( 'dinoai-admin' ),
				DINOAI_VERSION
			);

			wp_enqueue_script(
				'dinoai-classic',
				DINOAI_URL . 'admin/js/classic.js',
				array( 'jquery', 'wp-i18n', 'wp-api-fetch' ),
				DINOAI_VERSION,
				true
			);

			wp_set_script_translations( 'dinoai-classic', 'dinoai', DINOAI_PATH . 'languages' );

			wp_add_inline_script(
				'dinoai-classic',
				'window.dinoaiAdmin = ' . wp_json_encode( $this->js_config() ) . ';',
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
		$asset_file = DINOAI_PATH . 'build/index.asset.php';
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
			'version'      => DINOAI_VERSION,
		);

		wp_enqueue_style(
			'dinoai-editor',
			DINOAI_URL . 'admin/css/editor.css',
			array( 'wp-components' ),
			DINOAI_VERSION
		);

		wp_enqueue_script(
			'dinoai-editor',
			DINOAI_URL . 'build/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_set_script_translations( 'dinoai-editor', 'dinoai', DINOAI_PATH . 'languages' );

		wp_add_inline_script(
			'dinoai-editor',
			'window.dinoaiEditor = ' . wp_json_encode( $this->js_config() ) . ';',
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
		$settings = '<a href="' . esc_url( admin_url( 'admin.php?page=dinoai-settings' ) ) . '">' . esc_html__( 'Settings', 'dinoai' ) . '</a>';
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

		$config = array(
			'restUrl'        => rest_url( 'dinoai/v1/' ),
			'settingsUrl'    => admin_url( 'admin.php?page=dinoai-settings' ),
			'configured'     => $this->plugin->settings()->is_configured(),
			'modules'        => array(
				'content' => $this->plugin->settings()->is_module_enabled( 'content' ),
				'seo'     => $this->plugin->settings()->is_module_enabled( 'seo' ),
				'images'  => $this->plugin->settings()->is_module_enabled( 'images' ),
				'audit'   => $this->plugin->settings()->is_module_enabled( 'audit' ),
			),
			'supportsVision' => $this->plugin->ai()->supports_vision(),
			'postId'         => $post_id,
		);

		/**
		 * Filters editor/admin JS config. Never include API keys.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, mixed> $config Config.
		 */
		return apply_filters( 'dinoai_js_config', $config );
	}
}
