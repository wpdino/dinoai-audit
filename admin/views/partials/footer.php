<?php
/**
 * Shared WPDINO admin footer.
 *
 * @package WPDINO\DinoAIAudit
 */

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAIAudit\Admin\Admin_Layout;

$dinoai_audit_home_url    = Admin_Layout::utm_url( 'https://wpdino.com', 'footer_home_link' );
$dinoai_audit_docs_url    = Admin_Layout::utm_url( 'https://wpdino.com/docs/dinoai-audit/', 'footer_documentation' );
$dinoai_audit_support_url = Admin_Layout::utm_url( 'https://wordpress.org/support/plugin/dinoai-audit/', 'footer_support' );
$dinoai_audit_brand_url   = Admin_Layout::utm_url( 'https://wpdino.com', 'footer_brand_link' );
?>
<div class="wpdino-footer">
	<div class="wpdino-footer-content">
		<div class="wpdino-footer-left">
			<p>
				<?php
				echo wp_kses(
					sprintf(
						/* translators: 1: plugin version, 2: WPDINO website link */
						__( 'DinoAI Audit v%1$s by %2$s', 'dinoai-audit' ),
						esc_html( DINOAI_AUDIT_VERSION ),
						'<a href="' . esc_url( $dinoai_audit_brand_url ) . '" target="_blank" rel="noopener noreferrer">WPDINO</a>'
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
				<a href="<?php echo esc_url( $dinoai_audit_home_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'WPDINO', 'dinoai-audit' ); ?></a>
				<span>|</span>
				<a href="<?php echo esc_url( $dinoai_audit_docs_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'dinoai-audit' ); ?></a>
				<span>|</span>
				<a href="<?php echo esc_url( $dinoai_audit_support_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Support', 'dinoai-audit' ); ?></a>
			</div>
			<div class="wpdino-footer-social">
				<a href="https://www.facebook.com/wpdinocom" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Follow WPDINO on Facebook', 'dinoai-audit' ); ?>">
					<span class="dashicons dashicons-facebook-alt"></span>
				</a>
				<a href="https://x.com/wpdinocom" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Follow WPDINO on X (Twitter)', 'dinoai-audit' ); ?>">
					<span class="dashicons dashicons-twitter"></span>
				</a>
				<a href="https://www.instagram.com/_wpdino_/" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Follow WPDINO on Instagram', 'dinoai-audit' ); ?>">
					<span class="dashicons dashicons-camera"></span>
				</a>
			</div>
		</div>
	</div>
</div>
