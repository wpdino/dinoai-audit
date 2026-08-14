<?php
/**
 * Shared WPDINO admin footer.
 *
 * @package WPDINO\DinoAI
 */

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAI\Admin\Admin_Layout;

$home_url    = Admin_Layout::utm_url( 'https://wpdino.com', 'footer_home_link' );
$docs_url    = Admin_Layout::utm_url( 'https://wpdino.com/docs/dinoai/', 'footer_documentation' );
$support_url = Admin_Layout::utm_url( 'https://wordpress.org/support/plugin/dinoai/', 'footer_support' );
$brand_url   = Admin_Layout::utm_url( 'https://wpdino.com', 'footer_brand_link' );
?>
<div class="wpdino-footer">
	<div class="wpdino-footer-content">
		<div class="wpdino-footer-left">
			<p>
				<?php
				echo wp_kses(
					sprintf(
						/* translators: 1: plugin version, 2: WPDINO website link */
						__( 'DinoAI v%1$s by %2$s', 'dinoai' ),
						esc_html( DINOAI_VERSION ),
						'<a href="' . esc_url( $brand_url ) . '" target="_blank" rel="noopener noreferrer">WPDINO</a>'
					),
					array(
						'a' => array(
							'href'   => array(),
							'target' => array(),
							'rel'    => array(),
						),
					)
				);
				?>
			</p>
		</div>
		<div class="wpdino-footer-right">
			<div class="wpdino-footer-links">
				<a href="<?php echo esc_url( $home_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'WPDINO', 'dinoai' ); ?></a>
				<span>|</span>
				<a href="<?php echo esc_url( $docs_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'dinoai' ); ?></a>
				<span>|</span>
				<a href="<?php echo esc_url( $support_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Support', 'dinoai' ); ?></a>
			</div>
			<div class="wpdino-footer-social">
				<a href="https://www.facebook.com/wpdinocom" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Follow WPDINO on Facebook', 'dinoai' ); ?>">
					<span class="dashicons dashicons-facebook-alt"></span>
				</a>
				<a href="https://x.com/wpdinocom" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Follow WPDINO on X (Twitter)', 'dinoai' ); ?>">
					<span class="dashicons dashicons-twitter"></span>
				</a>
				<a href="https://www.instagram.com/_wpdino_/" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Follow WPDINO on Instagram', 'dinoai' ); ?>">
					<span class="dashicons dashicons-camera"></span>
				</a>
			</div>
		</div>
	</div>
</div>
