<?php
/**
 * Site audit screen.
 *
 * @package WPDINO\DinoAI
 *
 * @var array<string, mixed> $last
 */

defined( 'ABSPATH' ) || exit;

$dinoai_header_title   = __( 'Welcome to DinoAI!', 'dinoai' );
$dinoai_header_tagline = __( 'A basic audit of available WordPress data. This is not a complete security, accessibility, SEO, or performance audit.', 'dinoai' );
?>
<div class="wpdino-settings-wrap">
	<?php require DINOAI_PATH . 'admin/views/partials/header.php'; ?>

	<div class="wpdino-main">
		<div class="wpdino-content">
			<div class="wpdino-card">
				<div class="wpdino-tab-content active dinoai-audit-panel">
					<div class="dinoai-audit-toolbar">
						<div>
							<h3 class="wpdino-section-title">
								<span class="dashicons dashicons-chart-bar"></span>
								<?php esc_html_e( 'Site health', 'dinoai' ); ?>
							</h3>
							<p class="wpdino-section-description"><?php esc_html_e( 'A readable score, category breakdown, and clear next steps from data already on this site.', 'dinoai' ); ?></p>
						</div>
						<div class="dinoai-inline-actions">
							<button type="button" class="wpdino-btn wpdino-btn-primary" id="dinoai-run-audit">
								<span class="dashicons dashicons-update"></span>
								<?php esc_html_e( 'Run basic audit', 'dinoai' ); ?>
							</button>
							<span class="dinoai-test-status" id="dinoai-audit-status" aria-live="polite"></span>
						</div>
					</div>

					<div id="dinoai-audit-results">
						<?php if ( isset( $last['score'] ) ) : ?>
							<?php
							$score = $last;
							require DINOAI_PATH . 'admin/views/partials/audit-results.php';
							?>
						<?php else : ?>
							<div class="dinoai-empty">
								<span class="dinoai-tile-icon" aria-hidden="true">
									<span class="dashicons dashicons-chart-bar"></span>
								</span>
								<h3><?php esc_html_e( 'No audit yet', 'dinoai' ); ?></h3>
								<p><?php esc_html_e( 'Run a basic audit to see your site score, category breakdown, and recommended next steps.', 'dinoai' ); ?></p>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<?php require DINOAI_PATH . 'admin/views/partials/sidebar.php'; ?>
	</div>

	<?php require DINOAI_PATH . 'admin/views/partials/footer.php'; ?>
</div>
