<?php
/**
 * Settings sidebar: Pro upsell, docs, and review cards.
 *
 * @package WPDINO\DinoAIAudit
 */

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAIAudit\Admin\Admin_Layout;

$dinoai_audit_docs_url    = Admin_Layout::utm_url( 'https://wpdino.com/docs/dinoai-audit/', 'sidebar_documentation' );
$dinoai_audit_support_url = Admin_Layout::utm_url( 'https://wordpress.org/support/plugin/dinoai-audit/', 'sidebar_support' );
$dinoai_audit_review_url  = 'https://wordpress.org/plugins/dinoai-audit/#reviews';
$dinoai_audit_pro_url     = Admin_Layout::utm_url( Admin_Layout::PRO_URL, 'pro_upgrade_button' );
$dinoai_audit_show_pro    = ! Admin_Layout::is_pro_active();
?>
<div class="wpdino-sidebar">
	<?php if ( $dinoai_audit_show_pro ) : ?>
		<div class="wpdino-card wpdino-pro-card">
			<div class="wpdino-pro-header">
				<div class="wpdino-pro-badge">
					<?php esc_html_e( 'PRO', 'dinoai-audit' ); ?>
				</div>
				<h2><?php esc_html_e( 'Upgrade to DinoAI Audit PRO', 'dinoai-audit' ); ?></h2>
				<p><?php esc_html_e( 'Unlock SEO scoring, internal links, schema tools, advanced audit checks, and bulk AI workflows.', 'dinoai-audit' ); ?></p>

				<div class="wpdino-pro-features">
					<ul>
						<li>
							<span class="dashicons dashicons-yes"></span>
							<?php esc_html_e( 'DinoAI Audit SEO Score', 'dinoai-audit' ); ?>
						</li>
						<li>
							<span class="dashicons dashicons-yes"></span>
							<?php esc_html_e( 'Internal linking and orphan scan', 'dinoai-audit' ); ?>
						</li>
						<li>
							<span class="dashicons dashicons-yes"></span>
							<?php esc_html_e( 'Schema detect and generate', 'dinoai-audit' ); ?>
						</li>
						<li>
							<span class="dashicons dashicons-yes"></span>
							<?php esc_html_e( 'Advanced site audit checks', 'dinoai-audit' ); ?>
						</li>
						<li>
							<span class="dashicons dashicons-yes"></span>
							<?php esc_html_e( 'Bulk AI queues', 'dinoai-audit' ); ?>
						</li>
						<li>
							<span class="dashicons dashicons-yes"></span>
							<?php esc_html_e( 'Priority support', 'dinoai-audit' ); ?>
						</li>
					</ul>
				</div>

				<div class="wpdino-pro-cta">
					<a href="<?php echo esc_url( $dinoai_audit_pro_url ); ?>" target="_blank" rel="noopener noreferrer" class="wpdino-btn wpdino-btn-primary">
						<?php esc_html_e( 'Get DinoAI Audit PRO', 'dinoai-audit' ); ?>
						<span class="dashicons dashicons-arrow-right-alt"></span>
					</a>
					<p><?php esc_html_e( '30-day money-back guarantee', 'dinoai-audit' ); ?></p>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="wpdino-card wpdino-review-card">
		<div class="wpdino-review-header">
			<h2><?php esc_html_e( 'Need a hand?', 'dinoai-audit' ); ?></h2>
			<p><?php esc_html_e( 'Connect your OpenAI key, test the connection, then use DinoAI Audit in the editor. Docs and support are a click away.', 'dinoai-audit' ); ?></p>
			<div class="wpdino-review-cta">
				<a href="<?php echo esc_url( $dinoai_audit_docs_url ); ?>" target="_blank" rel="noopener noreferrer" class="wpdino-btn wpdino-btn-primary">
					<?php esc_html_e( 'Read the documentation', 'dinoai-audit' ); ?>
					<span class="dashicons dashicons-arrow-right-alt"></span>
				</a>
				<p>
					<a href="<?php echo esc_url( $dinoai_audit_support_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get support', 'dinoai-audit' ); ?></a>
				</p>
			</div>
		</div>
	</div>

	<div class="wpdino-card wpdino-review-card">
		<div class="wpdino-review-header">
			<div class="wpdino-review-stars" aria-hidden="true">
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
			</div>
			<h2><?php esc_html_e( 'Enjoying DinoAI Audit?', 'dinoai-audit' ); ?></h2>
			<p><?php esc_html_e( 'If these tools help your WordPress workflow, a quick 5-star review on WordPress.org means a lot to our small team.', 'dinoai-audit' ); ?></p>
			<div class="wpdino-review-cta">
				<a href="<?php echo esc_url( $dinoai_audit_review_url ); ?>" target="_blank" rel="noopener noreferrer" class="wpdino-btn wpdino-btn-primary">
					<?php esc_html_e( 'Leave a 5-Star Review', 'dinoai-audit' ); ?>
					<span class="dashicons dashicons-arrow-right-alt"></span>
				</a>
				<p><?php esc_html_e( 'It only takes a minute', 'dinoai-audit' ); ?></p>
			</div>
		</div>
	</div>
</div>
