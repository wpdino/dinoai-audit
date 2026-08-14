<?php
/**
 * Plugin deactivation.
 *
 * @package WPDINO\DinoAIAudit
 */

namespace WPDINO\DinoAIAudit;

defined( 'ABSPATH' ) || exit;

/**
 * Runs on plugin deactivation.
 *
 * @since 1.0.0
 */
class Deactivator {

	/**
	 * Deactivation callback.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function deactivate() {
		delete_transient( 'dinoai_audit_activation_redirect' );
	}
}
