<?php
/**
 * PSR-4-style autoloader for DinoAI classes.
 *
 * @package WPDINO\DinoAI
 */

namespace WPDINO\DinoAI;

defined( 'ABSPATH' ) || exit;

/**
 * Maps namespaced classes to WordPress-style class files.
 *
 * @since 1.0.0
 */
class Autoloader {

	/**
	 * Namespace prefix.
	 *
	 * @var string
	 */
	const PREFIX = 'WPDINO\\DinoAI\\';

	/**
	 * Registers the autoloader.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Loads a class file if it belongs to this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class_name Fully-qualified class name.
	 * @return void
	 */
	public static function autoload( $class_name ) {
		if ( 0 !== strpos( $class_name, self::PREFIX ) ) {
			return;
		}

		$relative = substr( $class_name, strlen( self::PREFIX ) );
		$parts    = explode( '\\', $relative );
		$name     = array_pop( $parts );

		if ( preg_match( '/_Interface$/', $name ) ) {
			$base = 'interface-' . str_replace( '_', '-', strtolower( preg_replace( '/_Interface$/', '', $name ) ) );
		} else {
			$base = 'class-' . str_replace( '_', '-', strtolower( $name ) );
		}

		$sub  = implode( '/', $parts );
		$path = DINOAI_PATH . 'includes/' . ( $sub ? $sub . '/' : '' ) . $base . '.php';

		if ( is_readable( $path ) ) {
			require_once $path;
		}
	}
}
