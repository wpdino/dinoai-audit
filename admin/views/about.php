<?php
/**
 * About screen.
 *
 * @package WPDINO\DinoAI
 */

defined( 'ABSPATH' ) || exit;

$dinoai_header_title   = __( 'Welcome to DinoAI!', 'dinoai' );
$dinoai_header_tagline = __( 'AI Toolkit for WordPress', 'dinoai' );
?>
<div class="wpdino-settings-wrap">
	<?php require DINOAI_PATH . 'admin/views/partials/header.php'; ?>

	<div class="wpdino-main">
		<div class="wpdino-content">
			<div class="wpdino-card">
				<div class="wpdino-tab-content active">
					<?php require DINOAI_PATH . 'admin/views/about-content.php'; ?>
				</div>
			</div>
		</div>

		<?php require DINOAI_PATH . 'admin/views/partials/sidebar.php'; ?>
	</div>

	<?php require DINOAI_PATH . 'admin/views/partials/footer.php'; ?>
</div>
