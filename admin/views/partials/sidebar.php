<?php
/**
 * Settings sidebar: docs and review cards.
 *
 * @package WPDINO\DinoAI
 */

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAI\Admin\Admin_Layout;

$docs_url    = Admin_Layout::utm_url( 'https://wpdino.com/docs/dinoai/', 'sidebar_documentation' );
$support_url = Admin_Layout::utm_url( 'https://wordpress.org/support/plugin/dinoai/', 'sidebar_support' );
$review_url  = 'https://wordpress.org/plugins/dinoai/#reviews';
?>
<div class="wpdino-sidebar">
	<div class="wpdino-card wpdino-review-card">
		<div class="wpdino-review-header">
			<h2><?php esc_html_e( 'Need a hand?', 'dinoai' ); ?></h2>
			<p><?php esc_html_e( 'Connect your OpenAI key, test the connection, then use DinoAI in the editor. Docs and support are a click away.', 'dinoai' ); ?></p>
			<div class="wpdino-review-cta">
				<a href="<?php echo esc_url( $docs_url ); ?>" target="_blank" rel="noopener noreferrer" class="wpdino-btn wpdino-btn-primary">
					<?php esc_html_e( 'Read the documentation', 'dinoai' ); ?>
					<span class="dashicons dashicons-arrow-right-alt"></span>
				</a>
				<p>
					<a href="<?php echo esc_url( $support_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get support', 'dinoai' ); ?></a>
				</p>
			</div>
		</div>
	</div>

	<div class="wpdino-card wpdino-review-card">
		<div class="wpdino-review-header">
			<div class="wpdino-review-stars" aria-hidden="true">
				<?php for ( $i = 0; $i < 5; $i++ ) : ?>
					<span class="dashicons dashicons-star-filled"></span>
				<?php endfor; ?>
			</div>
			<h2><?php esc_html_e( 'Enjoying DinoAI?', 'dinoai' ); ?></h2>
			<p><?php esc_html_e( 'If these tools help your WordPress workflow, a quick 5-star review on WordPress.org means a lot to our small team.', 'dinoai' ); ?></p>
			<div class="wpdino-review-cta">
				<a href="<?php echo esc_url( $review_url ); ?>" target="_blank" rel="noopener noreferrer" class="wpdino-btn wpdino-btn-primary">
					<?php esc_html_e( 'Leave a 5-Star Review', 'dinoai' ); ?>
					<span class="dashicons dashicons-arrow-right-alt"></span>
				</a>
				<p><?php esc_html_e( 'It only takes a minute', 'dinoai' ); ?></p>
			</div>
		</div>
	</div>
</div>
