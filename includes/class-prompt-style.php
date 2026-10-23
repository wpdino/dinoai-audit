<?php
/**
 * Applies writing tone and custom instructions to prompts.
 *
 * @package WPDINO\DinoAIAudit
 */

namespace WPDINO\DinoAIAudit;

defined( 'ABSPATH' ) || exit;

/**
 * Injects free writing-style settings into content (and SEO) prompts.
 *
 * @since 1.1.0
 */
class Prompt_Style {

	/**
	 * Settings.
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 *
	 * @param Settings $settings Settings.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Registers filters.
	 *
	 * @since 1.1.0
	 *
	 * @param Loader $loader Hook loader.
	 * @return void
	 */
	public function register( Loader $loader ) {
		$loader->add_filter( 'dinoai_audit_prompt', $this, 'apply_style', 10, 3 );
	}

	/**
	 * Prepends tone and custom instructions for content and SEO prompts.
	 *
	 * @since 1.1.0
	 *
	 * @param string               $prompt     Prompt text.
	 * @param string               $prompt_key Prompt key.
	 * @param array<string, mixed> $context    Context.
	 * @return string
	 */
	public function apply_style( $prompt, $prompt_key, $context ) {
		unset( $context );

		if ( ! is_string( $prompt ) || ! is_string( $prompt_key ) ) {
			return $prompt;
		}

		if ( 0 !== strpos( $prompt_key, 'content.' ) && 0 !== strpos( $prompt_key, 'seo.' ) ) {
			return $prompt;
		}

		$parts = array();

		$tone_text = Settings::tone_instruction( (string) $this->settings->get( 'tone', 'default' ) );

		if ( '' !== $tone_text ) {
			$parts[] = $tone_text;
		}

		$custom = (string) $this->settings->get( 'custom_instructions', '' );
		$custom = trim( $custom );

		if ( '' !== $custom ) {
			$parts[] = 'Additional site instructions (follow when relevant): ' . $custom;
		}

		if ( empty( $parts ) ) {
			return $prompt;
		}

		return implode( "\n\n", $parts ) . "\n\n" . $prompt;
	}
}
