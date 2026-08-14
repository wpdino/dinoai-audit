<?php
/**
 * Classic editor metabox.
 *
 * @package WPDINO\DinoAIAudit
 */

defined( 'ABSPATH' ) || exit;

$actions = \WPDINO\DinoAIAudit\Modules\Content::actions();
?>
<div class="dinoai-audit-classic" data-dinoai-audit-classic="1">
	<?php if ( ! dinoai_audit()->settings()->is_configured() ) : ?>
		<p><?php esc_html_e( 'No AI provider configured.', 'dinoai-audit' ); ?></p>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=dinoai-audit-settings#provider' ) ); ?>"><?php esc_html_e( 'Connect an AI provider in DinoAI Audit → Settings.', 'dinoai-audit' ); ?></a></p>
	<?php else : ?>
		<p class="description"><?php esc_html_e( 'Paste or type text, choose an action, then copy the result. Content is never replaced automatically.', 'dinoai-audit' ); ?></p>
		<label class="screen-reader-text" for="dinoai-audit-classic-input"><?php esc_html_e( 'Content to send to DinoAI Audit', 'dinoai-audit' ); ?></label>
		<textarea id="dinoai-audit-classic-input" class="widefat" rows="5"></textarea>
		<p class="dinoai-audit-classic-actions">
			<?php foreach ( $actions as $key => $label ) : ?>
				<button type="button" class="button dinoai-audit-classic-action" data-prompt-key="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></button>
			<?php endforeach; ?>
		</p>
		<div class="dinoai-audit-classic-status" aria-live="polite"></div>
		<label class="screen-reader-text" for="dinoai-audit-classic-result"><?php esc_html_e( 'DinoAI Audit result', 'dinoai-audit' ); ?></label>
		<textarea id="dinoai-audit-classic-result" class="widefat" rows="5" readonly></textarea>
		<p>
			<button type="button" class="button" id="dinoai-audit-classic-copy" disabled><?php esc_html_e( 'Copy result', 'dinoai-audit' ); ?></button>
		</p>
	<?php endif; ?>
</div>
