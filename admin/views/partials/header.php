<?php
/**
 * Shared WPDINO admin header.
 *
 * @package WPDINO\DinoAI
 *
 * @var string $dinoai_header_title   Header title.
 * @var string $dinoai_header_tagline Header tagline.
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $dinoai_header_title ) ) {
	$dinoai_header_title = __( 'Welcome to DinoAI!', 'dinoai' );
}

if ( ! isset( $dinoai_header_tagline ) ) {
	$dinoai_header_tagline = __( 'AI Toolkit for WordPress', 'dinoai' );
}
?>
<div class="wpdino-header">
	<div class="wpdino-header-content">
		<div class="wpdino-header-content-left">
			<h1><?php echo esc_html( $dinoai_header_title ); ?></h1>
			<p class="wpdino-tagline"><?php echo esc_html( $dinoai_header_tagline ); ?></p>
		</div>
		<div class="wpdino-header-content-right">
			<span class="wpdino-version">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: plugin version */
						__( 'v%s', 'dinoai' ),
						DINOAI_VERSION
					)
				);
				?>
			</span>
		</div>
	</div>
</div>
