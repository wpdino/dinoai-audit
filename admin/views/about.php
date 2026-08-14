<?php
/**
 * About screen.
 *
 * @package WPDINO\DinoAIAudit
 */

defined( 'ABSPATH' ) || exit;

$dinoai_audit_header_title   = __( 'Welcome to DinoAI Audit!', 'dinoai-audit' );
$dinoai_audit_header_tagline = __( 'AI-powered WordPress website audits and optimization', 'dinoai-audit' );
?>
<div class="wpdino-settings-wrap">
	<?php require DINOAI_AUDIT_PATH . 'admin/views/partials/header.php'; ?>

	<div class="wpdino-main">
		<div class="wpdino-content">
			<div class="wpdino-card">
				<div class="wpdino-tab-content active">
					<?php require DINOAI_AUDIT_PATH . 'admin/views/about-content.php'; ?>
				</div>
			</div>
		</div>

		<?php require DINOAI_AUDIT_PATH . 'admin/views/partials/sidebar.php'; ?>
	</div>

	<?php require DINOAI_AUDIT_PATH . 'admin/views/partials/footer.php'; ?>
</div>
