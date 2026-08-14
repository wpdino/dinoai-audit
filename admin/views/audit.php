<?php
/**
 * Site audit screen.
 *
 * @package WPDINO\DinoAIAudit
 *
 * @var array<string, mixed> $last
 */

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAIAudit\Modules\Site_Audit;

$screen                      = Site_Audit::screen();
$dinoai_audit_header_title   = __( 'Welcome to DinoAI Audit!', 'dinoai-audit' );
$dinoai_audit_header_tagline = $screen['tagline'];
?>
<div class="wpdino-settings-wrap">
	<?php require DINOAI_AUDIT_PATH . 'admin/views/partials/header.php'; ?>

	<div class="wpdino-main">
		<div class="wpdino-content">
			<div class="wpdino-card">
				<div class="wpdino-tab-content active dinoai-audit-panel">
					<div class="dinoai-audit-toolbar">
						<div>
							<h3 class="wpdino-section-title">
								<span class="dashicons dashicons-chart-bar"></span>
								<span><?php echo esc_html( $screen['heading'] ); ?></span>
								<?php if ( '' !== $screen['badge'] ) : ?>
									<span class="dinoai-audit-pro-badge"><?php echo esc_html( $screen['badge'] ); ?></span>
								<?php endif; ?>
							</h3>
							<p class="wpdino-section-description"><?php echo esc_html( $screen['description'] ); ?></p>
						</div>
						<div class="dinoai-audit-inline-actions">
							<button type="button" class="wpdino-btn wpdino-btn-primary" id="dinoai-audit-run-audit">
								<span class="dashicons dashicons-update"></span>
								<?php echo esc_html( $screen['button'] ); ?>
							</button>
							<span class="dinoai-audit-test-status" id="dinoai-audit-status" aria-live="polite"></span>
						</div>
					</div>

					<div id="dinoai-audit-results">
						<?php if ( isset( $last['score'] ) ) : ?>
							<?php
							$score = $last;
							require DINOAI_AUDIT_PATH . 'admin/views/partials/audit-results.php';
							?>
						<?php else : ?>
							<div class="dinoai-audit-empty">
								<span class="dinoai-audit-tile-icon" aria-hidden="true">
									<span class="dashicons dashicons-chart-bar"></span>
								</span>
								<h3>
									<span><?php esc_html_e( 'No audit yet', 'dinoai-audit' ); ?></span>
									<?php if ( '' !== $screen['badge'] ) : ?>
										<span class="dinoai-audit-pro-badge"><?php echo esc_html( $screen['badge'] ); ?></span>
									<?php endif; ?>
								</h3>
								<p><?php echo esc_html( $screen['empty'] ); ?></p>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<?php require DINOAI_AUDIT_PATH . 'admin/views/partials/sidebar.php'; ?>
	</div>

	<?php require DINOAI_AUDIT_PATH . 'admin/views/partials/footer.php'; ?>
</div>
