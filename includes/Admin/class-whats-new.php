<?php
/**
 * Dismissible “What’s new” notice after plugin updates.
 *
 * @package WPDINO\DinoAIAudit
 */

namespace WPDINO\DinoAIAudit\Admin;

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAIAudit\Loader;

/**
 * Flags a version bump and shows a short changelog until dismissed.
 *
 * @since 1.0.1
 */
class Whats_New {

	/**
	 * Option storing the version for which the notice should appear.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'dinoai_audit_whats_new_version';

	/**
	 * User meta: last dismissed what’s-new version.
	 *
	 * @var string
	 */
	const DISMISS_KEY = 'dinoai_audit_whats_new_dismissed';

	/**
	 * Registers hooks.
	 *
	 * @since 1.0.1
	 *
	 * @param Loader $loader Hook loader.
	 * @return void
	 */
	public function register( Loader $loader ) {
		$loader->add_action( 'admin_init', $this, 'maybe_flag_update' );
		$loader->add_action( 'admin_notices', $this, 'render' );
		$loader->add_action( 'admin_post_dinoai_audit_dismiss_whats_new', $this, 'dismiss' );
	}

	/**
	 * Detects upgrades and queues the notice. Skips fresh installs.
	 *
	 * @since 1.0.1
	 *
	 * @return void
	 */
	public function maybe_flag_update() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$stored = get_option( 'dinoai_audit_version', '' );

		if ( DINOAI_AUDIT_VERSION === $stored ) {
			return;
		}

		if ( is_string( $stored ) && '' !== $stored && version_compare( $stored, DINOAI_AUDIT_VERSION, '<' ) ) {
			update_option( self::OPTION_KEY, DINOAI_AUDIT_VERSION, false );
		}

		update_option( 'dinoai_audit_version', DINOAI_AUDIT_VERSION, false );
	}

	/**
	 * Renders the notice on DinoAI Audit admin screens.
	 *
	 * @since 1.0.1
	 *
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$show_for = get_option( self::OPTION_KEY, '' );

		if ( DINOAI_AUDIT_VERSION !== $show_for ) {
			return;
		}

		if ( get_user_meta( get_current_user_id(), self::DISMISS_KEY, true ) === DINOAI_AUDIT_VERSION ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || empty( $screen->id ) || false === strpos( $screen->id, 'dinoai-audit' ) ) {
			return;
		}

		$dismiss = wp_nonce_url(
			admin_url( 'admin-post.php?action=dinoai_audit_dismiss_whats_new' ),
			'dinoai_audit_dismiss_whats_new'
		);

		$items = $this->changelog_items();
		?>
		<div class="notice notice-info dinoai-audit-whats-new">
			<p><strong><?php echo esc_html( sprintf( /* translators: %s: plugin version */ __( 'What’s new in DinoAI Audit %s', 'dinoai-audit' ), DINOAI_AUDIT_VERSION ) ); ?></strong></p>
			<?php if ( ! empty( $items ) ) : ?>
				<ul style="list-style:disc;margin-left:1.25em;">
					<?php foreach ( $items as $item ) : ?>
						<li><?php echo esc_html( $item ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( $dismiss ); ?>"><?php esc_html_e( 'Got it', 'dinoai-audit' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Dismisses the notice for the current user and version.
	 *
	 * @since 1.0.1
	 *
	 * @return void
	 */
	public function dismiss() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage DinoAI Audit.', 'dinoai-audit' ) );
		}

		check_admin_referer( 'dinoai_audit_dismiss_whats_new' );

		update_user_meta( get_current_user_id(), self::DISMISS_KEY, DINOAI_AUDIT_VERSION );
		delete_option( self::OPTION_KEY );

		$redirect = wp_get_referer();

		if ( ! $redirect ) {
			$redirect = admin_url( 'admin.php?page=dinoai-audit' );
		}

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Changelog bullets for the current release notice.
	 *
	 * @since 1.0.1
	 *
	 * @return string[]
	 */
	protected function changelog_items() {
		return array(
			__( 'Writing tone presets for content and SEO drafts.', 'dinoai-audit' ),
			__( 'Site-wide custom instructions for AI actions.', 'dinoai-audit' ),
			__( 'Copy SEO title and meta description to Yoast or Rank Math.', 'dinoai-audit' ),
			__( 'Setup wizard: connect, test, then try Improve.', 'dinoai-audit' ),
		);
	}
}
