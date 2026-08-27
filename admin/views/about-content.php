<?php
/**
 * About content.
 *
 * @package WPDINO\DinoAIAudit
 */

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAIAudit\Admin\Admin_Layout;

$dinoai_audit_home_url    = Admin_Layout::utm_url( 'https://wpdino.com', 'about_home' );
$dinoai_audit_docs_url    = Admin_Layout::utm_url( 'https://wpdino.com/docs/dinoai-audit/', 'about_documentation' );
$dinoai_audit_support_url = Admin_Layout::utm_url( 'https://wpdino.com/support/', 'about_support' );
?>
<div class="wpdino-section">
	<h3 class="wpdino-section-title">
		<span class="dashicons dashicons-info"></span>
		<?php esc_html_e( 'About DinoAI Audit', 'dinoai-audit' ); ?>
	</h3>
	<p class="wpdino-section-description">
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: plugin version */
				__( 'Version %s · AI-powered WordPress website audits and optimization', 'dinoai-audit' ),
				DINOAI_AUDIT_VERSION
			)
		);
		?>
	</p>
</div>

<p class="dinoai-audit-about-lead"><?php esc_html_e( 'DinoAI Audit brings practical AI tools into the WordPress dashboard. Review every result before you insert or replace anything.', 'dinoai-audit' ); ?></p>

<div class="dinoai-audit-about-grid">
	<div class="dinoai-audit-about-card">
		<span class="dinoai-audit-tile-icon" aria-hidden="true">
			<span class="dashicons dashicons-edit"></span>
		</span>
		<h4><?php esc_html_e( 'Content', 'dinoai-audit' ); ?></h4>
		<p><?php esc_html_e( 'Improve, rewrite, shorten, expand, or summarize selected text.', 'dinoai-audit' ); ?></p>
	</div>
	<div class="dinoai-audit-about-card">
		<span class="dinoai-audit-tile-icon" aria-hidden="true">
			<span class="dashicons dashicons-chart-area"></span>
		</span>
		<h4><?php esc_html_e( 'SEO', 'dinoai-audit' ); ?></h4>
		<p><?php esc_html_e( 'Draft titles, meta descriptions, and keyword ideas you can copy.', 'dinoai-audit' ); ?></p>
	</div>
	<div class="dinoai-audit-about-card">
		<span class="dinoai-audit-tile-icon" aria-hidden="true">
			<span class="dashicons dashicons-format-image"></span>
		</span>
		<h4><?php esc_html_e( 'Images', 'dinoai-audit' ); ?></h4>
		<p><?php esc_html_e( 'Generate accessibility-focused alt text, then confirm before saving.', 'dinoai-audit' ); ?></p>
	</div>
	<div class="dinoai-audit-about-card">
		<span class="dinoai-audit-tile-icon" aria-hidden="true">
			<span class="dashicons dashicons-chart-bar"></span>
		</span>
		<h4><?php esc_html_e( 'Audit', 'dinoai-audit' ); ?></h4>
		<p><?php esc_html_e( 'Run a basic site check with a score and recommended next steps.', 'dinoai-audit' ); ?></p>
	</div>
</div>

<p class="dinoai-audit-about-links">
	<a href="<?php echo esc_url( $dinoai_audit_home_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'WPDINO', 'dinoai-audit' ); ?></a>
	<span aria-hidden="true"> · </span>
	<a href="<?php echo esc_url( $dinoai_audit_docs_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'dinoai-audit' ); ?></a>
	<span aria-hidden="true"> · </span>
	<a href="<?php echo esc_url( $dinoai_audit_support_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Support', 'dinoai-audit' ); ?></a>
</p>

<div class="dinoai-audit-privacy-card">
	<span class="dinoai-audit-tile-icon" aria-hidden="true">
		<span class="dashicons dashicons-lock"></span>
	</span>
	<h3><?php esc_html_e( 'Privacy', 'dinoai-audit' ); ?></h3>
	<p><?php esc_html_e( 'DinoAI Audit does not collect visitor data. When a logged-in user starts an action or tests the connection, selected text, SEO source text, or an image used for alt text is sent to OpenAI with your API key. The basic site audit stays on this site. Usage counts are stored here; full prompts and responses are not.', 'dinoai-audit' ); ?></p>
	<p>
		<a href="https://openai.com/policies/terms-of-use" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'OpenAI Terms of Use', 'dinoai-audit' ); ?></a>
		<span aria-hidden="true"> · </span>
		<a href="https://openai.com/policies/privacy-policy" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'OpenAI Privacy Policy', 'dinoai-audit' ); ?></a>
	</p>
</div>
