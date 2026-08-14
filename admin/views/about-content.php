<?php
/**
 * About content.
 *
 * @package WPDINO\DinoAI
 */

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAI\Admin\Admin_Layout;

$home_url    = Admin_Layout::utm_url( 'https://wpdino.com', 'about_home' );
$docs_url    = Admin_Layout::utm_url( 'https://wpdino.com/docs/dinoai/', 'about_documentation' );
$support_url = Admin_Layout::utm_url( 'https://wpdino.com/support/', 'about_support' );
?>
<div class="wpdino-section">
	<h3 class="wpdino-section-title">
		<span class="dashicons dashicons-info"></span>
		<?php esc_html_e( 'About DinoAI', 'dinoai' ); ?>
	</h3>
	<p class="wpdino-section-description">
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: plugin version */
				__( 'Version %s · AI Toolkit for WordPress', 'dinoai' ),
				DINOAI_VERSION
			)
		);
		?>
	</p>
</div>

<p class="dinoai-about-lead"><?php esc_html_e( 'DinoAI brings practical AI tools into the WordPress dashboard. Review every result before you insert or replace anything.', 'dinoai' ); ?></p>

<div class="dinoai-about-grid">
	<div class="dinoai-about-card">
		<span class="dinoai-tile-icon" aria-hidden="true">
			<span class="dashicons dashicons-edit"></span>
		</span>
		<h4><?php esc_html_e( 'Content', 'dinoai' ); ?></h4>
		<p><?php esc_html_e( 'Improve, rewrite, shorten, expand, or summarize selected text.', 'dinoai' ); ?></p>
	</div>
	<div class="dinoai-about-card">
		<span class="dinoai-tile-icon" aria-hidden="true">
			<span class="dashicons dashicons-chart-area"></span>
		</span>
		<h4><?php esc_html_e( 'SEO', 'dinoai' ); ?></h4>
		<p><?php esc_html_e( 'Draft titles, meta descriptions, and keyword ideas you can copy.', 'dinoai' ); ?></p>
	</div>
	<div class="dinoai-about-card">
		<span class="dinoai-tile-icon" aria-hidden="true">
			<span class="dashicons dashicons-format-image"></span>
		</span>
		<h4><?php esc_html_e( 'Images', 'dinoai' ); ?></h4>
		<p><?php esc_html_e( 'Generate accessibility-focused alt text, then confirm before saving.', 'dinoai' ); ?></p>
	</div>
	<div class="dinoai-about-card">
		<span class="dinoai-tile-icon" aria-hidden="true">
			<span class="dashicons dashicons-chart-bar"></span>
		</span>
		<h4><?php esc_html_e( 'Audit', 'dinoai' ); ?></h4>
		<p><?php esc_html_e( 'Run a basic site check with a score and recommended next steps.', 'dinoai' ); ?></p>
	</div>
</div>

<p class="dinoai-about-links">
	<a href="<?php echo esc_url( $home_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'WPDINO', 'dinoai' ); ?></a>
	<span aria-hidden="true"> · </span>
	<a href="<?php echo esc_url( $docs_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'dinoai' ); ?></a>
	<span aria-hidden="true"> · </span>
	<a href="<?php echo esc_url( $support_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Support', 'dinoai' ); ?></a>
</p>

<div class="dinoai-privacy-card">
	<span class="dinoai-tile-icon" aria-hidden="true">
		<span class="dashicons dashicons-lock"></span>
	</span>
	<h3><?php esc_html_e( 'Privacy', 'dinoai' ); ?></h3>
	<p><?php esc_html_e( 'When you run a DinoAI action, the selected content is sent to the configured AI provider using your API key. Nothing is sent until you start an action. DinoAI stores usage counts on this site, not full prompts or responses.', 'dinoai' ); ?></p>
	<p>
		<a href="https://openai.com/policies/privacy-policy" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'OpenAI privacy policy', 'dinoai' ); ?></a>
	</p>
</div>
