<?php
/**
 * Plugin Name:       DinoAI Audit
 * Plugin URI:        https://wpdino.com/dinoai-audit/
 * Description:       AI-powered WordPress website audits and optimization. Improve content, generate SEO text, write image alt text, and run a site audit.
 * Version:           1.0.1
 * Requires at least: 6.4
 * Tested up to:      7.1
 * Requires PHP:      7.4
 * Author:            WPDINO
 * Author URI:        https://wpdino.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dinoai-audit
 * Domain Path:       /languages
 *
 * Privacy Policy: This plugin processes data in the following ways:
 *
 * 1. AI Content Generation: When a logged-in user with the required capability starts
 *    a DinoAI Audit action, selected text, post title and content used for SEO drafts,
 *    or an image used for alt text is transmitted to the OpenAI API. Nothing is sent
 *    automatically in the background. OpenAI may use API data to improve their services
 *    as outlined in their privacy policy. Content is never written into a post until
 *    the user copies, inserts, replaces, or saves the result.
 *
 * 2. Connection Test: Clicking Test connection sends a short test prompt and the
 *    configured API key to OpenAI to confirm the key works.
 *
 * 3. Site Audit: The basic site audit runs on this WordPress site and does not send
 *    post content to OpenAI.
 *
 * 4. Plugin Settings: The OpenAI API key and usage counts are stored locally in the
 *    WordPress database. The API key is transmitted only to OpenAI when making API
 *    requests. The plugin does not store full prompts or full AI responses.
 *
 * 5. No Tracking: This plugin does not track visitors or collect analytics.
 *
 * For more information about OpenAI data handling:
 * - Terms of Use: https://openai.com/policies/terms-of-use
 * - Privacy Policy: https://openai.com/policies/privacy-policy
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
