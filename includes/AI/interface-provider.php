<?php
/**
 * AI provider interface.
 *
 * @package WPDINO\DinoAI
 */

namespace WPDINO\DinoAI\AI;

defined( 'ABSPATH' ) || exit;

/**
 * Contract for AI providers.
 *
 * @since 1.0.0
 */
interface Provider_Interface {

	/**
	 * Provider slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * Human-readable label.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_label();

	/**
	 * Whether this provider can analyze images.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function supports_vision();

	/**
	 * Generates plain text.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $prompt  Prompt text.
	 * @param array<string, mixed> $options Request options.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function generate_text( $prompt, array $options = array() );

	/**
	 * Generates structured JSON.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $prompt  Prompt text.
	 * @param array<string, mixed> $options Request options.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function generate_structured( $prompt, array $options = array() );

	/**
	 * Analyzes an image and returns text.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $prompt    Prompt text.
	 * @param string               $image_ref Data URL or remote image URL.
	 * @param array<string, mixed> $options   Request options.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function analyze_image( $prompt, $image_ref, array $options = array() );

	/**
	 * Minimal request used to verify credentials.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|\WP_Error
	 */
	public function test_connection();
}
