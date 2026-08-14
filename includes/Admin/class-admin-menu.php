<?php
/**
 * Admin menu.
 *
 * @package WPDINO\DinoAI
 */

namespace WPDINO\DinoAI\Admin;

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAI\Loader;
use WPDINO\DinoAI\Plugin;

/**
 * Registers the DinoAI admin pages.
 *
 * @since 1.0.0
 */
class Admin_Menu {

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
		$loader->add_action( 'admin_menu', $this, 'add_pages' );
	}

	/**
	 * Adds menu pages.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_pages() {
		$capability = 'manage_options';
		$icon       = $this->menu_icon();

		add_menu_page(
			__( 'DinoAI', 'dinoai' ),
			__( 'DinoAI', 'dinoai' ),
			$capability,
			'dinoai',
			array( $this, 'render_audit' ),
			$icon,
			58
		);

		add_submenu_page(
			'dinoai',
			__( 'Site Audit', 'dinoai' ),
			__( 'Site Audit', 'dinoai' ),
			$capability,
			'dinoai',
			array( $this, 'render_audit' )
		);

		add_submenu_page(
			'dinoai',
			__( 'DinoAI Settings', 'dinoai' ),
			__( 'Settings', 'dinoai' ),
			$capability,
			'dinoai-settings',
			array( $this, 'render_settings' )
		);

		add_submenu_page(
			'dinoai',
			__( 'About DinoAI', 'dinoai' ),
			__( 'About', 'dinoai' ),
			$capability,
			'dinoai-about',
			array( $this, 'render_about' )
		);

		/**
		 * Fires after DinoAI registers its admin pages.
		 *
		 * @since 1.0.0
		 *
		 * @param string $capability Required capability.
		 */
		do_action( 'dinoai_admin_menu', $capability );
	}

	/**
	 * Settings screen.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage DinoAI.', 'dinoai' ) );
		}

		$settings = $this->plugin->settings();
		$usage    = $this->plugin->usage()->get();
		$tab      = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab switch.

		$allowed_tabs = apply_filters( 'dinoai_settings_allowed_tabs', array( 'general', 'provider', 'usage', 'about' ) );

		if ( ! in_array( $tab, $allowed_tabs, true ) ) {
			$tab = 'general';
		}

		include DINOAI_PATH . 'admin/views/settings.php';
	}

	/**
	 * Audit screen.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_audit() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage DinoAI.', 'dinoai' ) );
		}

		$last = get_option( 'dinoai_last_audit', array() );

		if ( ! is_array( $last ) ) {
			$last = array();
		}

		include DINOAI_PATH . 'admin/views/audit.php';
	}

	/**
	 * About screen.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_about() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage DinoAI.', 'dinoai' ) );
		}

		include DINOAI_PATH . 'admin/views/about.php';
	}

	/**
	 * Base64 SVG menu icon.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function menu_icon() {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#a7aaad" d="M11 3.2l1.35 6.15 6.15 1.35-6.15 1.35L11 18.2l-1.35-6.15L3.5 10.7l6.15-1.35L11 3.2zm7.7 1.1.55 2.15 2.15.55-2.15.55-.55 2.15-.55-2.15-2.15-.55 2.15-.55.55-2.15z"/></svg>';

		return 'data:image/svg+xml;base64,' . base64_encode( $svg ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- menu icon data URI.
	}
}
