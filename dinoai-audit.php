<?php
/**
 * Plugin Name:       DinoAI Audit
 * Plugin URI:        https://wpdino.com/dinoai-audit/
 * Description:       AI-powered WordPress website audits and optimization. Improve content, generate SEO text, write image alt text, and run a site audit.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            WPDINO
 * Author URI:        https://wpdino.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dinoai-audit
 * Domain Path:       /languages
 *
 * @package WPDINO\DinoAIAudit
 */

defined( 'ABSPATH' ) || exit;

// Define plugin version.
if ( ! defined( 'DINOAI_AUDIT_VERSION' ) ) {
    define( 'DINOAI_AUDIT_VERSION', get_file_data( __FILE__, [ 'Version' ] )[0] );
}

define( 'DINOAI_AUDIT_FILE', __FILE__ );
define( 'DINOAI_AUDIT_PATH', plugin_dir_path( __FILE__ ) );
define( 'DINOAI_AUDIT_URL', plugin_dir_url( __FILE__ ) );
define( 'DINOAI_AUDIT_BASENAME', plugin_basename( __FILE__ ) );

require_once DINOAI_AUDIT_PATH . 'includes/class-autoloader.php';

WPDINO\DinoAIAudit\Autoloader::register();

register_activation_hook( DINOAI_AUDIT_FILE, array( 'WPDINO\\DinoAIAudit\\Activator', 'activate' ) );
register_deactivation_hook( DINOAI_AUDIT_FILE, array( 'WPDINO\\DinoAIAudit\\Deactivator', 'deactivate' ) );

/**
 * Returns the main plugin instance.
 *
 * @since 1.0.0
 *
 * @return WPDINO\DinoAIAudit\Plugin
 */
function dinoai_audit() {
	return WPDINO\DinoAIAudit\Plugin::instance();
}

add_action( 'plugins_loaded', 'dinoai_audit' );
