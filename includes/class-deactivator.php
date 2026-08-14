<?php
/**
 * Plugin deactivation.
 *
 * @package WPDINO\DinoAI
 */

namespace WPDINO\DinoAI;

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
		delete_transient( 'dinoai_activation_redirect' );
	}
}
