<?php
/**
 * Settings sidebar: docs and review cards.
 *
 * @package WPDINO\DinoAIAudit
 */

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAIAudit\Admin\Admin_Layout;

$dinoai_audit_docs_url    = Admin_Layout::utm_url( 'https://wpdino.com/docs/dinoai/', 'sidebar_documentation' );
$dinoai_audit_support_url = Admin_Layout::utm_url( 'https://wordpress.org/support/plugin/dinoai/', 'sidebar_support' );
$dinoai_audit_review_url  = 'https://wordpress.org/plugins/dinoai-audit/#reviews';
?>
<div class="wpdino-sidebar">
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
