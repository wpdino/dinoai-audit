<?php
/**
 * Setup wizard / onboarding checklist.
 *
 * @package WPDINO\DinoAIAudit
 */

namespace WPDINO\DinoAIAudit\Admin;

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAIAudit\Loader;
use WPDINO\DinoAIAudit\Settings;
use WPDINO\DinoAIAudit\Usage;

/**
 * Shows a dismissible setup checklist on DinoAI Audit admin screens.
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
	 * Usage tracker.
	 *
	 * @var Usage
	 */
	protected $usage;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Settings $settings Settings.
	 * @param Usage    $usage    Usage tracker.
	 */
	public function __construct( Settings $settings, Usage $usage ) {
		$this->settings = $settings;
		$this->usage    = $usage;
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
	 * Renders the wizard.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( get_user_meta( get_current_user_id(), self::DISMISS_KEY, true ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || empty( $screen->id ) || false === strpos( $screen->id, 'dinoai-audit' ) ) {
			return;
		}

		$steps = $this->steps();
		$total = count( $steps );
		$done  = 0;

		foreach ( $steps as $step ) {
			if ( ! empty( $step['done'] ) ) {
				++$done;
			}
		}

		if ( $done >= $total ) {
			return;
		}

		$percent = (int) round( ( $done / $total ) * 100 );
		$dismiss = wp_nonce_url(
			admin_url( 'admin-post.php?action=dinoai_audit_dismiss_onboarding' ),
			'dinoai_audit_dismiss_onboarding'
		);

		$next_index = 0;
		foreach ( $steps as $index => $step ) {
			if ( empty( $step['done'] ) ) {
				$next_index = $index;
				break;
			}
		}
		?>
		<div class="notice dinoai-audit-setup-wizard" role="region" aria-labelledby="dinoai-audit-setup-title">
			<div class="dinoai-audit-setup-card">
				<header class="dinoai-audit-setup-hero">
					<div class="dinoai-audit-setup-hero-copy">
						<span class="dinoai-audit-setup-badge" aria-hidden="true">
							<span class="dashicons dashicons-yes-alt"></span>
						</span>
						<div>
							<h2 id="dinoai-audit-setup-title"><?php esc_html_e( 'Get ready in three steps', 'dinoai-audit' ); ?></h2>
							<p><?php esc_html_e( 'Connect OpenAI, confirm it works, then run your first Improve — about two minutes.', 'dinoai-audit' ); ?></p>
						</div>
					</div>
					<div class="dinoai-audit-setup-progress" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: done, 2: total */ __( '%1$d of %2$d steps complete', 'dinoai-audit' ), $done, $total ) ); ?>">
						<div class="dinoai-audit-setup-progress-meta">
							<span class="dinoai-audit-setup-progress-count">
								<?php
								echo esc_html(
									sprintf(
										/* translators: 1: completed steps, 2: total steps */
										__( '%1$d / %2$d', 'dinoai-audit' ),
										$done,
										$total
									)
								);
								?>
							</span>
							<span class="dinoai-audit-setup-progress-label"><?php esc_html_e( 'complete', 'dinoai-audit' ); ?></span>
						</div>
						<div class="dinoai-audit-setup-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( (string) $percent ); ?>">
							<span class="dinoai-audit-setup-progress-fill" style="width: <?php echo esc_attr( (string) $percent ); ?>%;"></span>
						</div>
					</div>
				</header>

				<ol class="dinoai-audit-setup-steps">
					<?php foreach ( $steps as $index => $step ) : ?>
						<?php
						$is_done   = ! empty( $step['done'] );
						$is_current = ( ! $is_done && $index === $next_index );
						$item_class = 'dinoai-audit-setup-step';
						if ( $is_done ) {
							$item_class .= ' is-done';
						} elseif ( $is_current ) {
							$item_class .= ' is-current';
						}
						?>
						<li class="<?php echo esc_attr( $item_class ); ?>">
							<div class="dinoai-audit-setup-step-marker" aria-hidden="true">
								<?php if ( $is_done ) : ?>
									<span class="dashicons dashicons-yes"></span>
								<?php else : ?>
									<span class="dinoai-audit-setup-step-num"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
								<?php endif; ?>
							</div>
							<div class="dinoai-audit-setup-step-body">
								<span class="dinoai-audit-setup-step-icon dashicons <?php echo esc_attr( $step['icon'] ); ?>" aria-hidden="true"></span>
								<div class="dinoai-audit-setup-step-copy">
									<h3>
										<?php echo esc_html( $step['label'] ); ?>
										<?php if ( $is_done ) : ?>
											<span class="dinoai-audit-setup-step-status"><?php esc_html_e( 'Done', 'dinoai-audit' ); ?></span>
										<?php elseif ( $is_current ) : ?>
											<span class="dinoai-audit-setup-step-status is-next"><?php esc_html_e( 'Up next', 'dinoai-audit' ); ?></span>
										<?php endif; ?>
									</h3>
									<p><?php echo esc_html( $step['description'] ); ?></p>
								</div>
								<?php if ( $is_done ) : ?>
									<span class="dinoai-audit-setup-step-done-label">
										<span class="dashicons dashicons-saved" aria-hidden="true"></span>
										<span class="screen-reader-text"><?php esc_html_e( 'Completed', 'dinoai-audit' ); ?></span>
									</span>
								<?php elseif ( ! empty( $step['url'] ) && ! empty( $step['button'] ) ) : ?>
									<a
										class="wpdino-btn <?php echo $is_current ? 'wpdino-btn-primary' : 'wpdino-btn-secondary'; ?> dinoai-audit-setup-step-cta"
										href="<?php echo esc_url( $step['url'] ); ?>"
									>
										<?php echo esc_html( $step['button'] ); ?>
										<span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
									</a>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>

				<footer class="dinoai-audit-setup-footer">
					<p><?php esc_html_e( 'You can explore the rest of DinoAI Audit anytime — this checklist stays until you finish or dismiss it.', 'dinoai-audit' ); ?></p>
					<a class="dinoai-audit-setup-dismiss" href="<?php echo esc_url( $dismiss ); ?>"><?php esc_html_e( 'Skip for now', 'dinoai-audit' ); ?></a>
				</footer>
			</div>
		</div>
		<?php
	}

	/**
	 * Wizard steps with completion state.
	 *
	 * @since 1.1.0
	 *
	 * @return array<int, array<string, mixed>>
	 */
	protected function steps() {
		$configured = $this->settings->is_configured();
		$usage      = $this->usage->get();
		$by_module  = isset( $usage['by_module'] ) && is_array( $usage['by_module'] ) ? $usage['by_module'] : array();
		$tested     = $configured && ! empty( $by_module['test'] );
		$used       = ! empty( $by_module['content'] ) || ! empty( $by_module['seo'] ) || ! empty( $by_module['images'] );

		return array(
			array(
				'label'       => __( 'Connect your OpenAI API key', 'dinoai-audit' ),
				'description' => __( 'Paste a key in Settings → AI Provider and save.', 'dinoai-audit' ),
				'icon'        => 'dashicons-admin-network',
				'done'        => $configured,
				'url'         => admin_url( 'admin.php?page=dinoai-audit-settings#provider' ),
				'button'      => __( 'Open AI Provider', 'dinoai-audit' ),
			),
			array(
				'label'       => __( 'Test the connection', 'dinoai-audit' ),
				'description' => __( 'Run a quick check so you know the key works.', 'dinoai-audit' ),
				'icon'        => 'dashicons-yes',
				'done'        => $tested,
				'url'         => admin_url( 'admin.php?page=dinoai-audit-settings#provider' ),
				'button'      => __( 'Test connection', 'dinoai-audit' ),
			),
			array(
				'label'       => __( 'Try Improve on a post', 'dinoai-audit' ),
				'description' => __( 'Select text in the editor, then click Improve.', 'dinoai-audit' ),
				'icon'        => 'dashicons-edit',
				'done'        => $used,
				'url'         => admin_url( 'post-new.php' ),
				'button'      => __( 'New post', 'dinoai-audit' ),
			),
		);
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
