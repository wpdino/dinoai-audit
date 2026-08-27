<?php
/**
 * Main plugin class.
 *
 * @package WPDINO\DinoAIAudit
 */

namespace WPDINO\DinoAIAudit;

defined( 'ABSPATH' ) || exit;

/**
 * Boots DinoAI Audit and wires modules together.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	protected static $instance = null;

	/**
	 * Hook loader.
	 *
	 * @var Loader
	 */
	protected $loader;

	/**
	 * Settings handler.
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * AI manager.
	 *
	 * @var AI_Manager
	 */
	protected $ai;

	/**
	 * Prompt manager.
	 *
	 * @var Prompt_Manager
	 */
	protected $prompts;

	/**
	 * Usage tracker.
	 *
	 * @var Usage
	 */
	protected $usage;

	/**
	 * Registered modules.
	 *
	 * @var array<string, object>
	 */
	protected $modules = array();

	/**
	 * Returns the singleton instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->loader   = new Loader();
		$this->settings = new Settings();
		$this->prompts  = new Prompt_Manager();
		$this->usage    = new Usage();
		$this->ai       = new AI_Manager( $this->settings, $this->prompts, $this->usage );
	}

	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		$this->register_modules();

		$admin   = new Admin\Admin( $this );
		$menu    = new Admin\Admin_Menu( $this );
		$rest    = new REST( $this );
		$privacy = new Admin\Privacy();
		$onboard = new Admin\Onboarding( $this->settings );
		$whats   = new Admin\Whats_New();

		$admin->register( $this->loader );
		$menu->register( $this->loader );
		$rest->register( $this->loader );
		$privacy->register( $this->loader );
		$onboard->register( $this->loader );
		$whats->register( $this->loader );
		$this->settings->register( $this->loader );

		foreach ( $this->modules as $module ) {
			if ( is_object( $module ) && method_exists( $module, 'register' ) ) {
				$module->register( $this->loader );
			}
		}

		$this->loader->run();

		do_action( 'dinoai_audit_loaded', $this );
		do_action( 'dinoai_audit_modules_loaded', $this->modules );
	}

	/**
	 * Instantiates enabled modules.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function register_modules() {
		$classes = array(
			'content' => Modules\Content::class,
			'seo'     => Modules\SEO::class,
			'images'  => Modules\Images::class,
			'audit'   => Modules\Site_Audit::class,
		);

		/**
		 * Filters the module class map.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, class-string> $classes Module key => class name.
		 */
		$classes = apply_filters( 'dinoai_audit_modules', $classes );

		foreach ( $classes as $key => $class_name ) {
			if ( ! $this->settings->is_module_enabled( $key ) ) {
				continue;
			}

			if ( is_string( $class_name ) && class_exists( $class_name ) ) {
				$this->modules[ $key ] = new $class_name( $this );
			}
		}
	}

	/**
	 * Settings instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Settings
	 */
	public function settings() {
		return $this->settings;
	}

	/**
	 * AI manager instance.
	 *
	 * @since 1.0.0
	 *
	 * @return AI_Manager
	 */
	public function ai() {
		return $this->ai;
	}

	/**
	 * Prompt manager instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Prompt_Manager
	 */
	public function prompts() {
		return $this->prompts;
	}

	/**
	 * Usage tracker instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Usage
	 */
	public function usage() {
		return $this->usage;
	}

	/**
	 * Registered modules.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, object>
	 */
	public function modules() {
		return $this->modules;
	}

	/**
	 * Returns a module by key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Module key.
	 * @return object|null
	 */
	public function module( $key ) {
		return isset( $this->modules[ $key ] ) ? $this->modules[ $key ] : null;
	}
}
