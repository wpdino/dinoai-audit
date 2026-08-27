<?php
/**
 * Audit score, categories, and findings.
 *
 * @package WPDINO\DinoAIAudit
 *
 * @var array<string, mixed> $dinoai_audit_score
 */

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAIAudit\Admin\Admin_Layout;

$dinoai_audit_score_value   = isset( $dinoai_audit_score['score'] ) ? (int) $dinoai_audit_score['score'] : 0;
$dinoai_audit_band          = Admin_Layout::score_band( $dinoai_audit_score_value );
$dinoai_audit_categories    = isset( $dinoai_audit_score['categories'] ) && is_array( $dinoai_audit_score['categories'] ) ? $dinoai_audit_score['categories'] : array();
$dinoai_audit_issues        = isset( $dinoai_audit_score['issues'] ) && is_array( $dinoai_audit_score['issues'] ) ? $dinoai_audit_score['issues'] : array();
$dinoai_audit_issue_count   = isset( $dinoai_audit_score['count'] ) ? (int) $dinoai_audit_score['count'] : count( $dinoai_audit_issues );
$dinoai_audit_radius        = 52;
$dinoai_audit_circumference = 2 * M_PI * $dinoai_audit_radius;
$dinoai_audit_offset        = $dinoai_audit_circumference * ( 1 - ( max( 0, min( 100, $dinoai_audit_score_value ) ) / 100 ) );

$dinoai_audit_severity_rank = array(
	'error'   => 0,
	'warning' => 1,
	'notice'  => 2,
);

usort(
	$dinoai_audit_issues,
	static function ( $a, $b ) use ( $dinoai_audit_severity_rank ) {
		$left  = isset( $a['severity'], $dinoai_audit_severity_rank[ $a['severity'] ] ) ? $dinoai_audit_severity_rank[ $a['severity'] ] : 9;
		$right = isset( $b['severity'], $dinoai_audit_severity_rank[ $b['severity'] ] ) ? $dinoai_audit_severity_rank[ $b['severity'] ] : 9;
		return $left - $right;
	}
);
?>
<div class="dinoai-audit-health dinoai-audit-health--<?php echo esc_attr( $dinoai_audit_band['slug'] ); ?>">
	<div class="dinoai-audit-score-panel">
		<div class="dinoai-audit-score-hero">
			<div class="dinoai-audit-score-ring" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: site score */ __( 'Site score %d of 100', 'dinoai-audit' ), $dinoai_audit_score_value ) ); ?>">
				<svg viewBox="0 0 120 120" aria-hidden="true" focusable="false">
					<circle class="dinoai-audit-score-track" cx="60" cy="60" r="<?php echo esc_attr( (string) $dinoai_audit_radius ); ?>"></circle>
					<circle class="dinoai-audit-score-fill" cx="60" cy="60" r="<?php echo esc_attr( (string) $dinoai_audit_radius ); ?>" stroke-dasharray="<?php echo esc_attr( (string) round( $dinoai_audit_circumference, 2 ) ); ?>" stroke-dashoffset="<?php echo esc_attr( (string) round( $dinoai_audit_offset, 2 ) ); ?>"></circle>
				</svg>
				<div class="dinoai-audit-score-ring-value">
					<strong><?php echo esc_html( (string) $dinoai_audit_score_value ); ?></strong>
					<span>/ 100</span>
				</div>
			</div>
			<div class="dinoai-audit-score-copy">
				<p class="dinoai-audit-score-kicker"><?php esc_html_e( 'DinoAI Audit Site Score', 'dinoai-audit' ); ?></p>
				<h3 class="dinoai-audit-score-label"><?php echo esc_html( $dinoai_audit_band['label'] ); ?></h3>
				<p class="dinoai-audit-issue-count">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: number of issues */
							_n( '%d finding to review', '%d findings to review', $dinoai_audit_issue_count, 'dinoai-audit' ),
							$dinoai_audit_issue_count
						)
					);
					?>
				</p>
			</div>
		</div>

		<ul class="dinoai-audit-score-cats">
			<?php foreach ( $dinoai_audit_categories as $dinoai_audit_category_key => $dinoai_audit_value ) : ?>
				<?php $dinoai_audit_cat_value = (int) $dinoai_audit_value; ?>
				<li>
					<div class="dinoai-audit-cat-head">
						<span class="dinoai-audit-cat-icon dashicons <?php echo esc_attr( Admin_Layout::category_icon( $dinoai_audit_category_key ) ); ?>" aria-hidden="true"></span>
						<span><?php echo esc_html( Admin_Layout::category_label( $dinoai_audit_category_key ) ); ?></span>
						<strong><?php echo esc_html( (string) $dinoai_audit_cat_value ); ?></strong>
					</div>
					<div class="dinoai-audit-cat-bar" role="meter" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( (string) $dinoai_audit_cat_value ); ?>" aria-label="<?php echo esc_attr( Admin_Layout::category_label( $dinoai_audit_category_key ) ); ?>">
						<span style="width: <?php echo esc_attr( (string) $dinoai_audit_cat_value ); ?>%;"></span>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<?php if ( ! empty( $dinoai_audit_issues ) ) : ?>
		<div class="dinoai-audit-findings">
			<div class="dinoai-audit-findings-head">
				<div class="dinoai-audit-findings-intro">
					<h3><?php esc_html_e( 'Findings', 'dinoai-audit' ); ?></h3>
					<p><?php esc_html_e( 'Each item includes a solution. Nothing is changed until you act.', 'dinoai-audit' ); ?></p>
				</div>
				<div class="dinoai-audit-findings-filters" role="group" aria-label="<?php esc_attr_e( 'Filter findings', 'dinoai-audit' ); ?>">
					<label class="screen-reader-text" for="dinoai-audit-filter-severity"><?php esc_html_e( 'Severity', 'dinoai-audit' ); ?></label>
					<select id="dinoai-audit-filter-severity" class="dinoai-audit-filter-select" data-dinoai-audit-filter="severity">
						<option value=""><?php esc_html_e( 'All severities', 'dinoai-audit' ); ?></option>
						<option value="error"><?php esc_html_e( 'Error', 'dinoai-audit' ); ?></option>
						<option value="warning"><?php esc_html_e( 'Warning', 'dinoai-audit' ); ?></option>
						<option value="notice"><?php esc_html_e( 'Notice', 'dinoai-audit' ); ?></option>
					</select>
					<label class="screen-reader-text" for="dinoai-audit-filter-category"><?php esc_html_e( 'Category', 'dinoai-audit' ); ?></label>
					<select id="dinoai-audit-filter-category" class="dinoai-audit-filter-select" data-dinoai-audit-filter="category">
						<option value=""><?php esc_html_e( 'All categories', 'dinoai-audit' ); ?></option>
						<option value="seo"><?php esc_html_e( 'SEO', 'dinoai-audit' ); ?></option>
						<option value="content"><?php esc_html_e( 'Content', 'dinoai-audit' ); ?></option>
						<option value="accessibility"><?php esc_html_e( 'Accessibility', 'dinoai-audit' ); ?></option>
						<option value="technical"><?php esc_html_e( 'Technical', 'dinoai-audit' ); ?></option>
					</select>
				</div>
			</div>
			<p class="dinoai-audit-filter-empty" hidden><?php esc_html_e( 'No findings match these filters.', 'dinoai-audit' ); ?></p>
			<div class="dinoai-audit-issues">
				<?php foreach ( $dinoai_audit_issues as $dinoai_audit_issue ) : ?>
					<?php
					$dinoai_audit_severity = isset( $dinoai_audit_issue['severity'] ) ? $dinoai_audit_issue['severity'] : 'notice';
					$dinoai_audit_category = isset( $dinoai_audit_issue['category'] ) ? $dinoai_audit_issue['category'] : '';
					?>
					<article class="dinoai-audit-finding dinoai-audit-finding--<?php echo esc_attr( $dinoai_audit_severity ); ?>" data-severity="<?php echo esc_attr( $dinoai_audit_severity ); ?>" data-category="<?php echo esc_attr( $dinoai_audit_category ); ?>">
						<div class="dinoai-audit-tile-icon dinoai-audit-finding-icon" aria-hidden="true">
							<span class="dashicons <?php echo esc_attr( Admin_Layout::category_icon( $dinoai_audit_category ) ); ?>"></span>
						</div>
						<div class="dinoai-audit-finding-body">
							<header class="dinoai-audit-finding-header">
								<h4><?php echo esc_html( $dinoai_audit_issue['title'] ); ?></h4>
								<div class="dinoai-audit-finding-tags">
									<span class="dinoai-audit-chip"><?php echo esc_html( Admin_Layout::category_label( $dinoai_audit_category ) ); ?></span>
									<span class="dinoai-audit-chip dinoai-audit-chip--<?php echo esc_attr( $dinoai_audit_severity ); ?>">
										<span class="dashicons <?php echo esc_attr( Admin_Layout::severity_icon( $dinoai_audit_severity ) ); ?>" aria-hidden="true"></span>
										<?php echo esc_html( Admin_Layout::severity_label( $dinoai_audit_severity ) ); ?>
									</span>
								</div>
							</header>
							<p class="dinoai-audit-finding-desc"><?php echo esc_html( $dinoai_audit_issue['description'] ); ?></p>
							<?php
							$dinoai_audit_finding_items     = isset( $dinoai_audit_issue['items'] ) && is_array( $dinoai_audit_issue['items'] ) ? $dinoai_audit_issue['items'] : array();
							$dinoai_audit_finding_total     = isset( $dinoai_audit_issue['count'] ) ? (int) $dinoai_audit_issue['count'] : count( $dinoai_audit_finding_items );
							$dinoai_audit_finding_remaining = $dinoai_audit_finding_total - count( $dinoai_audit_finding_items );
							?>
							<?php if ( ! empty( $dinoai_audit_finding_items ) ) : ?>
								<p class="dinoai-audit-finding-items-label"><?php esc_html_e( 'Affected content', 'dinoai-audit' ); ?></p>
								<ul class="dinoai-audit-finding-items">
									<?php foreach ( $dinoai_audit_finding_items as $dinoai_audit_finding_item ) : ?>
										<?php
										$dinoai_audit_item_title = isset( $dinoai_audit_finding_item['title'] ) ? (string) $dinoai_audit_finding_item['title'] : '';
										$dinoai_audit_item_url   = isset( $dinoai_audit_finding_item['edit_url'] ) ? (string) $dinoai_audit_finding_item['edit_url'] : '';
										?>
										<li>
											<?php if ( '' !== $dinoai_audit_item_url ) : ?>
												<a href="<?php echo esc_url( $dinoai_audit_item_url ); ?>"><?php echo esc_html( $dinoai_audit_item_title ); ?></a>
											<?php else : ?>
												<?php echo esc_html( $dinoai_audit_item_title ); ?>
											<?php endif; ?>
										</li>
									<?php endforeach; ?>
								</ul>
								<?php if ( $dinoai_audit_finding_remaining > 0 ) : ?>
									<p class="dinoai-audit-finding-more">
										<?php
										echo esc_html(
											sprintf(
												/* translators: %d: remaining item count */
												_n( 'and %d more', 'and %d more', $dinoai_audit_finding_remaining, 'dinoai-audit' ),
												$dinoai_audit_finding_remaining
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
									<p><?php echo esc_html( $dinoai_audit_issue['recommendation'] ); ?></p>
									<?php
									$dinoai_audit_action_url   = isset( $dinoai_audit_issue['action_url'] ) ? (string) $dinoai_audit_issue['action_url'] : '';
									$dinoai_audit_action_label = isset( $dinoai_audit_issue['action_label'] ) ? (string) $dinoai_audit_issue['action_label'] : '';
									?>
									<?php if ( '' !== $dinoai_audit_action_url && '' !== $dinoai_audit_action_label ) : ?>
										<p class="dinoai-audit-finding-action-cta">
											<a href="<?php echo esc_url( $dinoai_audit_action_url ); ?>"><?php echo esc_html( $dinoai_audit_action_label ); ?></a>
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

	<?php if ( ! empty( $dinoai_audit_score['disclaimer'] ) ) : ?>
		<p class="dinoai-audit-note"><?php echo esc_html( $dinoai_audit_score['disclaimer'] ); ?></p>
	<?php endif; ?>
</div>
