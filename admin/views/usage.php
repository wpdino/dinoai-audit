<?php
/**
 * Usage panel.
 *
 * @package WPDINO\DinoAIAudit
 *
 * @var array<string, mixed> $usage
 */

defined( 'ABSPATH' ) || exit;

$dinoai_audit_last = ! empty( $usage['last_request'] ) ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $usage['last_request'] ) : __( 'Never', 'dinoai-audit' );
?>
<div class="wpdino-section">
	<h3 class="wpdino-section-title">
		<span class="dashicons dashicons-chart-area"></span>
		<?php esc_html_e( 'Usage', 'dinoai-audit' ); ?>
	</h3>
	<p class="wpdino-section-description"><?php esc_html_e( 'Local request totals only. DinoAI Audit does not store prompts or AI responses.', 'dinoai-audit' ); ?></p>
</div>

<div class="dinoai-audit-stat-grid">
	<div class="dinoai-audit-stat">
		<span class="dinoai-audit-tile-icon" aria-hidden="true">
			<span class="dashicons dashicons-chart-bar"></span>
		</span>
		<span class="dinoai-audit-stat-label"><?php esc_html_e( 'Total requests', 'dinoai-audit' ); ?></span>
		<strong class="dinoai-audit-stat-value"><?php echo esc_html( number_format_i18n( (int) $usage['total_requests'] ) ); ?></strong>
	</div>
	<div class="dinoai-audit-stat">
		<span class="dinoai-audit-tile-icon" aria-hidden="true">
			<span class="dashicons dashicons-clock"></span>
		</span>
		<span class="dinoai-audit-stat-label"><?php esc_html_e( 'Last request', 'dinoai-audit' ); ?></span>
		<strong class="dinoai-audit-stat-value dinoai-audit-stat-value--small"><?php echo esc_html( $dinoai_audit_last ); ?></strong>
	</div>
	<div class="dinoai-audit-stat">
		<span class="dinoai-audit-tile-icon" aria-hidden="true">
			<span class="dashicons dashicons-arrow-down-alt"></span>
		</span>
		<span class="dinoai-audit-stat-label"><?php esc_html_e( 'Tokens in', 'dinoai-audit' ); ?></span>
		<strong class="dinoai-audit-stat-value"><?php echo esc_html( number_format_i18n( (int) $usage['tokens_in'] ) ); ?></strong>
	</div>
	<div class="dinoai-audit-stat">
		<span class="dinoai-audit-tile-icon" aria-hidden="true">
			<span class="dashicons dashicons-arrow-up-alt"></span>
		</span>
		<span class="dinoai-audit-stat-label"><?php esc_html_e( 'Tokens out', 'dinoai-audit' ); ?></span>
		<strong class="dinoai-audit-stat-value"><?php echo esc_html( number_format_i18n( (int) $usage['tokens_out'] ) ); ?></strong>
	</div>
</div>

<div class="dinoai-audit-usage-meta">
	<div>
		<span class="dinoai-audit-stat-label"><?php esc_html_e( 'Last module', 'dinoai-audit' ); ?></span>
		<strong><?php echo esc_html( $usage['last_module'] ? $usage['last_module'] : '—' ); ?></strong>
	</div>
	<div>
		<span class="dinoai-audit-stat-label"><?php esc_html_e( 'Last model', 'dinoai-audit' ); ?></span>
		<strong><?php echo esc_html( $usage['last_model'] ? $usage['last_model'] : '—' ); ?></strong>
	</div>
</div>

<h3 class="wpdino-section-title dinoai-audit-usage-subtitle"><?php esc_html_e( 'By module', 'dinoai-audit' ); ?></h3>
<ul class="dinoai-audit-module-usage">
	<?php foreach ( $usage['by_module'] as $dinoai_audit_module => $dinoai_audit_count ) : ?>
		<li>
			<span><?php echo esc_html( $dinoai_audit_module ); ?></span>
			<strong><?php echo esc_html( number_format_i18n( (int) $dinoai_audit_count ) ); ?></strong>
		</li>
	<?php endforeach; ?>
</ul>

<?php
/**
 * Fires after the usage breakdown.
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $usage Usage data.
 */
do_action( 'dinoai_audit_usage_panel', $usage );
?>
