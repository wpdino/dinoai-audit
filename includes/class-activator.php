<?php
/**
 * Plugin activation.
 *
 * @package WPDINO\DinoAIAudit
 */

namespace WPDINO\DinoAIAudit;

defined( 'ABSPATH' ) || exit;

/**
 * Runs on plugin activation.
 *
 * @since 1.0.0
 */
class Activator {

	/**
	 * Activation callback.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function activate() {
		if ( ! get_option( Settings::OPTION_KEY ) ) {
			add_option( Settings::OPTION_KEY, Settings::defaults(), '', false );
		}

		if ( ! get_option( Usage::OPTION_KEY ) ) {
			add_option( Usage::OPTION_KEY, Usage::defaults(), '', false );
		}

		update_option( 'dinoai_audit_version', DINOAI_AUDIT_VERSION, false );

		set_transient( 'dinoai_audit_activation_redirect', 1, 30 );
	}
}
