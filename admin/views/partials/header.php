<?php
/**
 * Shared WPDINO admin header.
 *
 * @package WPDINO\DinoAIAudit
 *
 * @var string $dinoai_audit_header_title   Header title.
 * @var string $dinoai_audit_header_tagline Header tagline.
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $dinoai_audit_header_title ) ) {
	$dinoai_audit_header_title = __( 'Welcome to DinoAI Audit!', 'dinoai-audit' );
}

if ( ! isset( $dinoai_audit_header_tagline ) ) {
	$dinoai_audit_header_tagline = __( 'AI-powered WordPress website audits and optimization', 'dinoai-audit' );
}
?>
<div class="wpdino-header">
	<div class="wpdino-header-content">
		<div class="wpdino-header-content-left">
			<h1><?php echo esc_html( $dinoai_audit_header_title ); ?></h1>
			<p class="wpdino-tagline"><?php echo esc_html( $dinoai_audit_header_tagline ); ?></p>
		</div>
		<div class="wpdino-header-content-right">
			<span class="wpdino-version">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: plugin version */
						__( 'v%s', 'dinoai-audit' ),
						DINOAI_AUDIT_VERSION
					)
				);
				?>
			</span>
		</div>
	</div>
</div>
