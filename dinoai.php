<?php
/**
 * Plugin Name:       DinoAI
 * Plugin URI:        https://wpdino.com/dinoai/
 * Description:       AI Toolkit for WordPress. Improve content, generate SEO text, write image alt text, and run a basic site audit.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            WPDINO
 * Author URI:        https://wpdino.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dinoai
 * Domain Path:       /languages
 *
 * @package WPDINO\DinoAI
 */

defined( 'ABSPATH' ) || exit;

define( 'DINOAI_VERSION', '1.0.0' );
define( 'DINOAI_FILE', __FILE__ );
define( 'DINOAI_PATH', plugin_dir_path( __FILE__ ) );
define( 'DINOAI_URL', plugin_dir_url( __FILE__ ) );
define( 'DINOAI_BASENAME', plugin_basename( __FILE__ ) );

require_once DINOAI_PATH . 'includes/class-autoloader.php';

WPDINO\DinoAI\Autoloader::register();

register_activation_hook( DINOAI_FILE, array( 'WPDINO\\DinoAI\\Activator', 'activate' ) );
register_deactivation_hook( DINOAI_FILE, array( 'WPDINO\\DinoAI\\Deactivator', 'deactivate' ) );

/**
 * Returns the main plugin instance.
 *
 * @since 1.0.0
 *
 * @return WPDINO\DinoAI\Plugin
 */
function dinoai() {
	return WPDINO\DinoAI\Plugin::instance();
}

add_action( 'plugins_loaded', 'dinoai' );
