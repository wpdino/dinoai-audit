<?php
/**
 * SEO module.
 *
 * @package WPDINO\DinoAI
 */

namespace WPDINO\DinoAI\Modules;

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAI\Loader;
use WPDINO\DinoAI\Plugin;

/**
 * Basic AI SEO assistance. Complements, rather than replaces, dedicated SEO plugins.
 *
 * @since 1.0.0
 */
class SEO {

	/**
	 * Meta key for generated meta description.
	 *
	 * @var string
	 */
	const META_DESCRIPTION_KEY = '_dinoai_meta_description';

	/**
	 * Plugin instance.
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Plugin $plugin Plugin instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Registers hooks.
	 *
	 * @since 1.0.0
	 *
	 * @param Loader $loader Hook loader.
	 * @return void
	 */
	public function register( Loader $loader ) {
		$loader->add_action( 'init', $this, 'register_meta' );
	}

	/**
	 * Registers post meta for generated descriptions.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_meta() {
		$post_types = get_post_types( array( 'public' => true ), 'names' );

		if ( ! is_array( $post_types ) ) {
			$post_types = array( 'post', 'page' );
		}

		unset( $post_types['attachment'] );

		$args = array(
			'show_in_rest'      => true,
			'single'            => true,
			'type'              => 'string',
			'auth_callback'     => array( $this, 'can_edit_meta' ),
			'sanitize_callback' => 'sanitize_textarea_field',
		);

		foreach ( $post_types as $post_type ) {
			register_post_meta( $post_type, self::META_DESCRIPTION_KEY, $args );
		}
	}

	/**
	 * Whether the current user can edit the post meta.
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $allowed  Whether allowed.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id  Post ID.
	 * @return bool
	 */
	public function can_edit_meta( $allowed, $meta_key, $post_id ) {
		unset( $allowed, $meta_key );
		return current_user_can( 'edit_post', $post_id );
	}
}
