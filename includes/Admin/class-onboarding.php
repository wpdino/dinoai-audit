<?php
/**
 * Lightweight onboarding notice.
 *
 * @package WPDINO\DinoAIAudit
 */

namespace WPDINO\DinoAIAudit\Admin;

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAIAudit\Loader;
use WPDINO\DinoAIAudit\Settings;

/**
 * Shows a dismissible welcome notice on plugin admin screens until the provider is configured.
 *
 * @since 1.0.0
 */
class Onboarding {

	/**
	 * User meta key.
	 *
	 * @var string
	 */
	const DISMISS_KEY = 'dinoai_audit_onboarding_dismissed';

	/**
	 * Settings.
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Settings $settings Settings.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
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
		$loader->add_action( 'admin_notices', $this, 'render' );
		$loader->add_action( 'admin_post_dinoai_audit_dismiss_onboarding', $this, 'dismiss' );
	}

	/**
	 * Renders the notice.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( $this->settings->is_configured() ) {
			return;
		}

		if ( get_user_meta( get_current_user_id(), self::DISMISS_KEY, true ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || empty( $screen->id ) || false === strpos( $screen->id, 'dinoai-audit' ) ) {
			return;
		}

		$dismiss = wp_nonce_url(
			admin_url( 'admin-post.php?action=dinoai_audit_dismiss_onboarding' ),
			'dinoai_audit_dismiss_onboarding'
		);
		?>
		<div class="notice notice-info dinoai-audit-onboarding">
			<p><strong><?php esc_html_e( 'Welcome to DinoAI Audit', 'dinoai-audit' ); ?></strong></p>
			<p><?php esc_html_e( 'Bring practical AI tools into your WordPress workflow.', 'dinoai-audit' ); ?></p>
			<ol>
				<li><?php esc_html_e( 'Connect your AI provider', 'dinoai-audit' ); ?></li>
				<li><?php esc_html_e( 'Test the connection', 'dinoai-audit' ); ?></li>
				<li><?php esc_html_e( 'Start improving your content', 'dinoai-audit' ); ?></li>
			</ol>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=dinoai-audit-settings#provider' ) ); ?>"><?php esc_html_e( 'Configure DinoAI Audit', 'dinoai-audit' ); ?></a>
				<a class="button" href="<?php echo esc_url( $dismiss ); ?>"><?php esc_html_e( 'Dismiss', 'dinoai-audit' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Dismisses the notice.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function dismiss() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage DinoAI Audit.', 'dinoai-audit' ) );
		}

		check_admin_referer( 'dinoai_audit_dismiss_onboarding' );

		update_user_meta( get_current_user_id(), self::DISMISS_KEY, 1 );

		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
		exit;
	}
}
