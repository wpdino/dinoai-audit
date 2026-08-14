<?php
/**
 * Hook loader.
 *
 * @package WPDINO\DinoAIAudit
 */

namespace WPDINO\DinoAIAudit;

defined( 'ABSPATH' ) || exit;

/**
 * Collects and registers WordPress actions and filters.
 *
 * @since 1.0.0
 */
class Loader {

	/**
	 * Registered actions.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	protected $actions = array();

	/**
	 * Registered filters.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	protected $filters = array();

	/**
	 * Adds an action.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook      Hook name.
	 * @param object $component Object instance.
	 * @param string $callback  Method name.
	 * @param int    $priority  Priority.
	 * @param int    $args      Accepted arguments.
	 * @return void
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $args = 1 ) {
		$this->actions[] = array(
			'hook'      => $hook,
			'component' => $component,
			'callback'  => $callback,
			'priority'  => $priority,
			'args'      => $args,
		);
	}

	/**
	 * Adds a filter.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook      Hook name.
	 * @param object $component Object instance.
	 * @param string $callback  Method name.
	 * @param int    $priority  Priority.
	 * @param int    $args      Accepted arguments.
	 * @return void
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $args = 1 ) {
		$this->filters[] = array(
			'hook'      => $hook,
			'component' => $component,
			'callback'  => $callback,
			'priority'  => $priority,
			'args'      => $args,
		);
	}

	/**
	 * Registers collected hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['args']
			);
		}

		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['args']
			);
		}
	}
}
