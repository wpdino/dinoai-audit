<?php
/**
 * Audit score, categories, and findings.
 *
 * @package WPDINO\DinoAIAudit
 *
 * @var array<string, mixed> $score
 */

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAIAudit\Admin\Admin_Layout;

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
<div class="dinoai-audit-health dinoai-audit-health--<?php echo esc_attr( $band['slug'] ); ?>">
	<div class="dinoai-audit-score-panel">
		<div class="dinoai-audit-score-hero">
			<div class="dinoai-audit-score-ring" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: site score */ __( 'Site score %d of 100', 'dinoai-audit' ), $score_value ) ); ?>">
				<svg viewBox="0 0 120 120" aria-hidden="true" focusable="false">
					<circle class="dinoai-audit-score-track" cx="60" cy="60" r="<?php echo esc_attr( (string) $radius ); ?>"></circle>
					<circle class="dinoai-audit-score-fill" cx="60" cy="60" r="<?php echo esc_attr( (string) $radius ); ?>" stroke-dasharray="<?php echo esc_attr( (string) round( $circumference, 2 ) ); ?>" stroke-dashoffset="<?php echo esc_attr( (string) round( $offset, 2 ) ); ?>"></circle>
				</svg>
				<div class="dinoai-audit-score-ring-value">
					<strong><?php echo esc_html( (string) $score_value ); ?></strong>
					<span>/ 100</span>
				</div>
			</div>
			<div class="dinoai-audit-score-copy">
				<p class="dinoai-audit-score-kicker"><?php esc_html_e( 'DinoAI Audit Site Score', 'dinoai-audit' ); ?></p>
				<h3 class="dinoai-audit-score-label"><?php echo esc_html( $band['label'] ); ?></h3>
				<p class="dinoai-audit-issue-count">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: number of issues */
							_n( '%d finding to review', '%d findings to review', $issue_count, 'dinoai-audit' ),
							$issue_count
						)
					);
					?>
				</p>
			</div>
		</div>

		<ul class="dinoai-audit-score-cats">
			<?php foreach ( $categories as $category_key => $value ) : ?>
				<?php $cat_value = (int) $value; ?>
				<li>
					<div class="dinoai-audit-cat-head">
						<span class="dinoai-audit-cat-icon dashicons <?php echo esc_attr( Admin_Layout::category_icon( $category_key ) ); ?>" aria-hidden="true"></span>
						<span><?php echo esc_html( Admin_Layout::category_label( $category_key ) ); ?></span>
						<strong><?php echo esc_html( (string) $cat_value ); ?></strong>
					</div>
					<div class="dinoai-audit-cat-bar" role="meter" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( (string) $cat_value ); ?>" aria-label="<?php echo esc_attr( Admin_Layout::category_label( $category_key ) ); ?>">
						<span style="width: <?php echo esc_attr( (string) $cat_value ); ?>%;"></span>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<?php if ( ! empty( $issues ) ) : ?>
		<div class="dinoai-audit-findings">
			<div class="dinoai-audit-findings-head">
				<h3><?php esc_html_e( 'Findings', 'dinoai-audit' ); ?></h3>
				<p><?php esc_html_e( 'Each item includes a solution. Nothing is changed until you act.', 'dinoai-audit' ); ?></p>
			</div>
			<div class="dinoai-audit-issues">
				<?php foreach ( $issues as $issue ) : ?>
					<?php
					$severity = isset( $issue['severity'] ) ? $issue['severity'] : 'notice';
					$category = isset( $issue['category'] ) ? $issue['category'] : '';
					?>
					<article class="dinoai-audit-finding dinoai-audit-finding--<?php echo esc_attr( $severity ); ?>">
						<div class="dinoai-audit-tile-icon dinoai-audit-finding-icon" aria-hidden="true">
							<span class="dashicons <?php echo esc_attr( Admin_Layout::category_icon( $category ) ); ?>"></span>
						</div>
						<div class="dinoai-audit-finding-body">
							<header class="dinoai-audit-finding-header">
								<h4><?php echo esc_html( $issue['title'] ); ?></h4>
								<div class="dinoai-audit-finding-tags">
									<span class="dinoai-audit-chip"><?php echo esc_html( Admin_Layout::category_label( $category ) ); ?></span>
									<span class="dinoai-audit-chip dinoai-audit-chip--<?php echo esc_attr( $severity ); ?>">
										<span class="dashicons <?php echo esc_attr( Admin_Layout::severity_icon( $severity ) ); ?>" aria-hidden="true"></span>
										<?php echo esc_html( Admin_Layout::severity_label( $severity ) ); ?>
									</span>
								</div>
							</header>
							<p class="dinoai-audit-finding-desc"><?php echo esc_html( $issue['description'] ); ?></p>
							<?php
							$finding_items     = isset( $issue['items'] ) && is_array( $issue['items'] ) ? $issue['items'] : array();
							$finding_total     = isset( $issue['count'] ) ? (int) $issue['count'] : count( $finding_items );
							$finding_remaining = $finding_total - count( $finding_items );
							?>
							<?php if ( ! empty( $finding_items ) ) : ?>
								<p class="dinoai-audit-finding-items-label"><?php esc_html_e( 'Affected content', 'dinoai-audit' ); ?></p>
								<ul class="dinoai-audit-finding-items">
									<?php foreach ( $finding_items as $finding_item ) : ?>
										<?php
										$item_title = isset( $finding_item['title'] ) ? (string) $finding_item['title'] : '';
										$item_url   = isset( $finding_item['edit_url'] ) ? (string) $finding_item['edit_url'] : '';
										?>
										<li>
											<?php if ( '' !== $item_url ) : ?>
												<a href="<?php echo esc_url( $item_url ); ?>"><?php echo esc_html( $item_title ); ?></a>
											<?php else : ?>
												<?php echo esc_html( $item_title ); ?>
											<?php endif; ?>
										</li>
									<?php endforeach; ?>
								</ul>
								<?php if ( $finding_remaining > 0 ) : ?>
									<p class="dinoai-audit-finding-more">
										<?php
										echo esc_html(
											sprintf(
												/* translators: %d: remaining item count */
												_n( 'and %d more', 'and %d more', $finding_remaining, 'dinoai-audit' ),
												$finding_remaining
											)
										);
										?>
									</p>
								<?php endif; ?>
							<?php endif; ?>
							<div class="dinoai-audit-finding-action">
								<span class="dashicons dashicons-lightbulb" aria-hidden="true"></span>
								<div>
									<span class="dinoai-audit-finding-action-label"><?php esc_html_e( 'Solution', 'dinoai-audit' ); ?></span>
									<p><?php echo esc_html( $issue['recommendation'] ); ?></p>
									<?php
									$action_url   = isset( $issue['action_url'] ) ? (string) $issue['action_url'] : '';
									$action_label = isset( $issue['action_label'] ) ? (string) $issue['action_label'] : '';
									?>
									<?php if ( '' !== $action_url && '' !== $action_label ) : ?>
										<p class="dinoai-audit-finding-action-cta">
											<a href="<?php echo esc_url( $action_url ); ?>"><?php echo esc_html( $action_label ); ?></a>
										</p>
									<?php endif; ?>
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
