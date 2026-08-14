<?php
/**
 * Classic editor metabox.
 *
 * @package WPDINO\DinoAI
 */

defined( 'ABSPATH' ) || exit;

$actions = \WPDINO\DinoAI\Modules\Content::actions();
?>
<div class="dinoai-classic" data-dinoai-classic="1">
	<?php if ( ! dinoai()->settings()->is_configured() ) : ?>
		<p><?php esc_html_e( 'No AI provider configured.', 'dinoai' ); ?></p>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=dinoai-settings#provider' ) ); ?>"><?php esc_html_e( 'Connect an AI provider in DinoAI → Settings.', 'dinoai' ); ?></a></p>
	<?php else : ?>
		<p class="description"><?php esc_html_e( 'Paste or type text, choose an action, then copy the result. Content is never replaced automatically.', 'dinoai' ); ?></p>
		<label class="screen-reader-text" for="dinoai-classic-input"><?php esc_html_e( 'Content to send to DinoAI', 'dinoai' ); ?></label>
		<textarea id="dinoai-classic-input" class="widefat" rows="5"></textarea>
		<p class="dinoai-classic-actions">
			<?php foreach ( $actions as $key => $label ) : ?>
				<button type="button" class="button dinoai-classic-action" data-prompt-key="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></button>
			<?php endforeach; ?>
		</p>
		<div class="dinoai-classic-status" aria-live="polite"></div>
		<label class="screen-reader-text" for="dinoai-classic-result"><?php esc_html_e( 'DinoAI result', 'dinoai' ); ?></label>
		<textarea id="dinoai-classic-result" class="widefat" rows="5" readonly></textarea>
		<p>
			<button type="button" class="button" id="dinoai-classic-copy" disabled><?php esc_html_e( 'Copy result', 'dinoai' ); ?></button>
		</p>
	<?php endif; ?>
</div>
