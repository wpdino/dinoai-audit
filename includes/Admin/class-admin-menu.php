<?php
/**
 * Admin menu.
 *
 * @package WPDINO\DinoAIAudit
 */

namespace WPDINO\DinoAIAudit\Admin;

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAIAudit\Loader;
use WPDINO\DinoAIAudit\Modules\Site_Audit;
use WPDINO\DinoAIAudit\Plugin;

/**
 * Registers the DinoAI Audit admin pages.
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
		$loader->add_action( 'admin_menu', $this, 'add_go_pro_link', 20 );
		$loader->add_action( 'admin_head', $this, 'go_pro_menu_css' );
		$loader->add_action( 'admin_footer', $this, 'go_pro_menu_js' );
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
			__( 'DinoAI Audit', 'dinoai-audit' ),
			__( 'DinoAI Audit', 'dinoai-audit' ),
			$capability,
			'dinoai-audit',
			array( $this, 'render_audit' ),
			$icon,
			58
		);

		add_submenu_page(
			'dinoai-audit',
			__( 'Site Audit', 'dinoai-audit' ),
			__( 'Site Audit', 'dinoai-audit' ),
			$capability,
			'dinoai-audit',
			array( $this, 'render_audit' )
		);

		add_submenu_page(
			'dinoai-audit',
			__( 'DinoAI Audit Settings', 'dinoai-audit' ),
			__( 'Settings', 'dinoai-audit' ),
			$capability,
			'dinoai-audit-settings',
			array( $this, 'render_settings' )
		);

		/**
		 * Fires after the main DinoAI Audit admin pages are registered.
		 * About is added after this hook so it stays last.
		 *
		 * @since 1.0.0
		 *
		 * @param string $capability Required capability.
		 */
		do_action( 'dinoai_audit_admin_menu', $capability );

		add_submenu_page(
			'dinoai-audit',
			__( 'About DinoAI Audit', 'dinoai-audit' ),
			__( 'About', 'dinoai-audit' ),
			$capability,
			'dinoai-audit-about',
			array( $this, 'render_about' )
		);
	}

	/**
	 * Adds an Upgrade to PRO submenu item when Pro is not active.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_go_pro_link() {
		if ( Admin_Layout::is_pro_active() ) {
			return;
		}

		global $submenu;

		if ( ! isset( $submenu['dinoai-audit'] ) ) {
			return;
		}

		$submenu['dinoai-audit'][] = array(
			esc_html__( 'Upgrade to PRO', 'dinoai-audit' ),
			'manage_options',
			Admin_Layout::go_pro_url(),
		);
	}

	/**
	 * Styles the Upgrade to PRO menu link.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function go_pro_menu_css() {
		if ( Admin_Layout::is_pro_active() ) {
			return;
		}

		$url = esc_url( Admin_Layout::go_pro_url() );
		?>
		<style>
			#adminmenu #toplevel_page_dinoai-audit a[href="<?php echo esc_attr( $url ); ?>"],
			#adminmenu #toplevel_page_dinoai-audit a[href*="dinoai-audit-pro"],
			#adminmenu #toplevel_page_dinoai-audit a.dinoai-audit-go-pro-menu-link {
				background-color: #1a8960;
				color: #fff;
				font-weight: 600;
			}
			#adminmenu #toplevel_page_dinoai-audit a[href="<?php echo esc_attr( $url ); ?>"]:hover,
			#adminmenu #toplevel_page_dinoai-audit a[href*="dinoai-audit-pro"]:hover,
			#adminmenu #toplevel_page_dinoai-audit a.dinoai-audit-go-pro-menu-link:hover {
				background-color: rgb(21, 116, 81);
				color: #fff;
			}
		</style>
		<?php
	}

	/**
	 * Opens the Upgrade to PRO menu link in a new tab.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function go_pro_menu_js() {
		if ( Admin_Layout::is_pro_active() ) {
			return;
		}
		?>
		<script>
			jQuery( document ).ready( function( $ ) {
				var $proLink = $( '.wp-submenu a[href*="dinoai-audit-pro"]' );
				if ( $proLink.length ) {
					$proLink.attr( 'target', '_blank' ).addClass( 'dinoai-audit-go-pro-menu-link' );
				}
			} );
		</script>
		<?php
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
			wp_die( esc_html__( 'You do not have permission to manage DinoAI Audit.', 'dinoai-audit' ) );
		}

		$settings = $this->plugin->settings();
		$usage    = $this->plugin->usage()->get();
		$tab      = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab switch.

		$allowed_tabs = apply_filters( 'dinoai_audit_settings_allowed_tabs', array( 'general', 'provider', 'usage', 'about' ) );

		if ( ! in_array( $tab, $allowed_tabs, true ) ) {
			$tab = 'general';
		}

		include DINOAI_AUDIT_PATH . 'admin/views/settings.php';
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
			wp_die( esc_html__( 'You do not have permission to manage DinoAI Audit.', 'dinoai-audit' ) );
		}

		$last = get_option( 'dinoai_audit_last_audit', array() );

		if ( ! is_array( $last ) ) {
			$last = array();
		}

		if ( ! empty( $last['issues'] ) && is_array( $last['issues'] ) ) {
			$audit = $this->plugin->module( 'audit' );

			if ( $audit instanceof Site_Audit ) {
				$last['issues'] = $audit->prepare_issues( $last['issues'] );
			}
		}

		include DINOAI_AUDIT_PATH . 'admin/views/audit.php';
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
			wp_die( esc_html__( 'You do not have permission to manage DinoAI Audit.', 'dinoai-audit' ) );
		}

		include DINOAI_AUDIT_PATH . 'admin/views/about.php';
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
