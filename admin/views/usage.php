<?php
/**
 * Usage panel.
 *
 * @package WPDINO\DinoAI
 *
 * @var array<string, mixed> $usage
 */

defined( 'ABSPATH' ) || exit;

$last = ! empty( $usage['last_request'] ) ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $usage['last_request'] ) : __( 'Never', 'dinoai' );
?>
<div class="wpdino-section">
	<h3 class="wpdino-section-title">
		<span class="dashicons dashicons-chart-area"></span>
		<?php esc_html_e( 'Usage', 'dinoai' ); ?>
	</h3>
	<p class="wpdino-section-description"><?php esc_html_e( 'Local request totals only. DinoAI does not store prompts or AI responses.', 'dinoai' ); ?></p>
</div>

<div class="dinoai-stat-grid">
	<div class="dinoai-stat">
		<span class="dinoai-tile-icon" aria-hidden="true">
			<span class="dashicons dashicons-chart-bar"></span>
		</span>
		<span class="dinoai-stat-label"><?php esc_html_e( 'Total requests', 'dinoai' ); ?></span>
		<strong class="dinoai-stat-value"><?php echo esc_html( number_format_i18n( (int) $usage['total_requests'] ) ); ?></strong>
	</div>
	<div class="dinoai-stat">
		<span class="dinoai-tile-icon" aria-hidden="true">
			<span class="dashicons dashicons-clock"></span>
		</span>
		<span class="dinoai-stat-label"><?php esc_html_e( 'Last request', 'dinoai' ); ?></span>
		<strong class="dinoai-stat-value dinoai-stat-value--small"><?php echo esc_html( $last ); ?></strong>
	</div>
	<div class="dinoai-stat">
		<span class="dinoai-tile-icon" aria-hidden="true">
			<span class="dashicons dashicons-arrow-down-alt"></span>
		</span>
		<span class="dinoai-stat-label"><?php esc_html_e( 'Tokens in', 'dinoai' ); ?></span>
		<strong class="dinoai-stat-value"><?php echo esc_html( number_format_i18n( (int) $usage['tokens_in'] ) ); ?></strong>
	</div>
	<div class="dinoai-stat">
		<span class="dinoai-tile-icon" aria-hidden="true">
			<span class="dashicons dashicons-arrow-up-alt"></span>
		</span>
		<span class="dinoai-stat-label"><?php esc_html_e( 'Tokens out', 'dinoai' ); ?></span>
		<strong class="dinoai-stat-value"><?php echo esc_html( number_format_i18n( (int) $usage['tokens_out'] ) ); ?></strong>
	</div>
</div>

<div class="dinoai-usage-meta">
	<div>
		<span class="dinoai-stat-label"><?php esc_html_e( 'Last module', 'dinoai' ); ?></span>
		<strong><?php echo esc_html( $usage['last_module'] ? $usage['last_module'] : '—' ); ?></strong>
	</div>
	<div>
		<span class="dinoai-stat-label"><?php esc_html_e( 'Last model', 'dinoai' ); ?></span>
		<strong><?php echo esc_html( $usage['last_model'] ? $usage['last_model'] : '—' ); ?></strong>
	</div>
</div>

<h3 class="wpdino-section-title dinoai-usage-subtitle"><?php esc_html_e( 'By module', 'dinoai' ); ?></h3>
<ul class="dinoai-module-usage">
	<?php foreach ( $usage['by_module'] as $module => $count ) : ?>
		<li>
			<span><?php echo esc_html( $module ); ?></span>
			<strong><?php echo esc_html( number_format_i18n( (int) $count ) ); ?></strong>
		</li>
	<?php endforeach; ?>
</ul>

<?php
/**
 * Fires after the Lite usage breakdown.
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $usage Usage data.
 */
do_action( 'dinoai_usage_panel', $usage );
?>
