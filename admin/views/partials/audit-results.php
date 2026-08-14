<?php
/**
 * Audit score, categories, and findings.
 *
 * @package WPDINO\DinoAI
 *
 * @var array<string, mixed> $score
 */

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAI\Admin\Admin_Layout;

$score_value   = isset( $score['score'] ) ? (int) $score['score'] : 0;
$band          = Admin_Layout::score_band( $score_value );
$categories    = isset( $score['categories'] ) && is_array( $score['categories'] ) ? $score['categories'] : array();
$issues        = isset( $score['issues'] ) && is_array( $score['issues'] ) ? $score['issues'] : array();
$issue_count   = isset( $score['count'] ) ? (int) $score['count'] : count( $issues );
$radius        = 52;
$circumference = 2 * M_PI * $radius;
$offset        = $circumference * ( 1 - ( max( 0, min( 100, $score_value ) ) / 100 ) );

$severity_rank = array(
	'error'   => 0,
	'warning' => 1,
	'notice'  => 2,
);

usort(
	$issues,
	static function ( $a, $b ) use ( $severity_rank ) {
		$left  = isset( $a['severity'], $severity_rank[ $a['severity'] ] ) ? $severity_rank[ $a['severity'] ] : 9;
		$right = isset( $b['severity'], $severity_rank[ $b['severity'] ] ) ? $severity_rank[ $b['severity'] ] : 9;
		return $left - $right;
	}
);
?>
<div class="dinoai-health dinoai-health--<?php echo esc_attr( $band['slug'] ); ?>">
	<div class="dinoai-score-panel">
		<div class="dinoai-score-hero">
			<div class="dinoai-score-ring" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: site score */ __( 'Site score %d of 100', 'dinoai' ), $score_value ) ); ?>">
				<svg viewBox="0 0 120 120" aria-hidden="true" focusable="false">
					<circle class="dinoai-score-track" cx="60" cy="60" r="<?php echo esc_attr( (string) $radius ); ?>"></circle>
					<circle class="dinoai-score-fill" cx="60" cy="60" r="<?php echo esc_attr( (string) $radius ); ?>" stroke-dasharray="<?php echo esc_attr( (string) round( $circumference, 2 ) ); ?>" stroke-dashoffset="<?php echo esc_attr( (string) round( $offset, 2 ) ); ?>"></circle>
				</svg>
				<div class="dinoai-score-ring-value">
					<strong><?php echo esc_html( (string) $score_value ); ?></strong>
					<span>/ 100</span>
				</div>
			</div>
			<div class="dinoai-score-copy">
				<p class="dinoai-score-kicker"><?php esc_html_e( 'DinoAI Site Score', 'dinoai' ); ?></p>
				<h3 class="dinoai-score-label"><?php echo esc_html( $band['label'] ); ?></h3>
				<p class="dinoai-issue-count">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: number of issues */
							_n( '%d finding to review', '%d findings to review', $issue_count, 'dinoai' ),
							$issue_count
						)
					);
					?>
				</p>
			</div>
		</div>

		<ul class="dinoai-score-cats">
			<?php foreach ( $categories as $category_key => $value ) : ?>
				<?php $cat_value = (int) $value; ?>
				<li>
					<div class="dinoai-cat-head">
						<span class="dinoai-cat-icon dashicons <?php echo esc_attr( Admin_Layout::category_icon( $category_key ) ); ?>" aria-hidden="true"></span>
						<span><?php echo esc_html( Admin_Layout::category_label( $category_key ) ); ?></span>
						<strong><?php echo esc_html( (string) $cat_value ); ?></strong>
					</div>
					<div class="dinoai-cat-bar" role="meter" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( (string) $cat_value ); ?>" aria-label="<?php echo esc_attr( Admin_Layout::category_label( $category_key ) ); ?>">
						<span style="width: <?php echo esc_attr( (string) $cat_value ); ?>%;"></span>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<?php if ( ! empty( $issues ) ) : ?>
		<div class="dinoai-findings">
			<div class="dinoai-findings-head">
				<h3><?php esc_html_e( 'Findings', 'dinoai' ); ?></h3>
				<p><?php esc_html_e( 'Each item includes a next step. Nothing is changed until you act.', 'dinoai' ); ?></p>
			</div>
			<div class="dinoai-issues">
				<?php foreach ( $issues as $issue ) : ?>
					<?php
					$severity = isset( $issue['severity'] ) ? $issue['severity'] : 'notice';
					$category = isset( $issue['category'] ) ? $issue['category'] : '';
					?>
					<article class="dinoai-finding dinoai-finding--<?php echo esc_attr( $severity ); ?>">
						<div class="dinoai-tile-icon dinoai-finding-icon" aria-hidden="true">
							<span class="dashicons <?php echo esc_attr( Admin_Layout::category_icon( $category ) ); ?>"></span>
						</div>
						<div class="dinoai-finding-body">
							<header class="dinoai-finding-header">
								<h4><?php echo esc_html( $issue['title'] ); ?></h4>
								<div class="dinoai-finding-tags">
									<span class="dinoai-chip"><?php echo esc_html( Admin_Layout::category_label( $category ) ); ?></span>
									<span class="dinoai-chip dinoai-chip--<?php echo esc_attr( $severity ); ?>">
										<span class="dashicons <?php echo esc_attr( Admin_Layout::severity_icon( $severity ) ); ?>" aria-hidden="true"></span>
										<?php echo esc_html( Admin_Layout::severity_label( $severity ) ); ?>
									</span>
								</div>
							</header>
							<p class="dinoai-finding-desc"><?php echo esc_html( $issue['description'] ); ?></p>
							<div class="dinoai-finding-action">
								<span class="dashicons dashicons-lightbulb" aria-hidden="true"></span>
								<div>
									<span class="dinoai-finding-action-label"><?php esc_html_e( 'Next step', 'dinoai' ); ?></span>
									<p><?php echo esc_html( $issue['recommendation'] ); ?></p>
								</div>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $score['disclaimer'] ) ) : ?>
		<p class="dinoai-audit-note"><?php echo esc_html( $score['disclaimer'] ); ?></p>
	<?php endif; ?>
</div>
