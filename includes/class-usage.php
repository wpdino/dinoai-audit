<?php
/**
 * Local usage tracking.
 *
 * @package WPDINO\DinoAI
 */

namespace WPDINO\DinoAI;

defined( 'ABSPATH' ) || exit;

/**
 * Stores lightweight request counts. Does not store prompts or responses.
 *
 * @since 1.0.0
 */
class Usage {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'dinoai_usage';

	/**
	 * Default usage payload.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults() {
		return array(
			'total_requests' => 0,
			'tokens_in'      => 0,
			'tokens_out'     => 0,
			'last_request'   => 0,
			'last_module'    => '',
			'last_model'     => '',
			'last_provider'  => '',
			'by_module'      => array(
				'content' => 0,
				'seo'     => 0,
				'images'  => 0,
				'audit'   => 0,
				'test'    => 0,
			),
		);
	}

	/**
	 * Returns stored usage data.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>
	 */
	public function get() {
		$stored = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$data = wp_parse_args( $stored, self::defaults() );

		if ( ! isset( $data['by_module'] ) || ! is_array( $data['by_module'] ) ) {
			$data['by_module'] = self::defaults()['by_module'];
		} else {
			$data['by_module'] = wp_parse_args( $data['by_module'], self::defaults()['by_module'] );
		}

		return $data;
	}

	/**
	 * Records a successful request.
	 *
	 * @since 1.0.0
	 *
	 * @param string $module    Module key.
	 * @param string $provider  Provider slug.
	 * @param string $model     Model id.
	 * @param int    $tokens_in Input tokens.
	 * @param int    $tokens_out Output tokens.
	 * @return void
	 */
	public function record( $module, $provider, $model, $tokens_in = 0, $tokens_out = 0 ) {
		$data = $this->get();

		$module   = sanitize_key( $module );
		$provider = sanitize_key( $provider );
		$model    = sanitize_text_field( $model );

		$data['total_requests'] = (int) $data['total_requests'] + 1;
		$data['tokens_in']      = (int) $data['tokens_in'] + max( 0, (int) $tokens_in );
		$data['tokens_out']     = (int) $data['tokens_out'] + max( 0, (int) $tokens_out );
		$data['last_request']   = time();
		$data['last_module']    = $module;
		$data['last_model']     = $model;
		$data['last_provider']  = $provider;

		if ( ! isset( $data['by_module'][ $module ] ) ) {
			$data['by_module'][ $module ] = 0;
		}

		$data['by_module'][ $module ] = (int) $data['by_module'][ $module ] + 1;

		update_option( self::OPTION_KEY, $data, false );
	}
}
