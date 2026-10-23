<?php
/**
 * Plugin settings.
 *
 * @package WPDINO\DinoAIAudit
 */

namespace WPDINO\DinoAIAudit;

defined( 'ABSPATH' ) || exit;

/**
 * Stores and sanitizes plugin options.
 *
 * @since 1.0.0
 */
class Settings {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'dinoai_audit_settings';

	/**
	 * Placeholder used when the stored key should be kept.
	 *
	 * @var string
	 */
	const KEY_PLACEHOLDER = '••••••••';

	/**
	 * Registers settings hooks.
	 *
	 * @since 1.0.0
	 *
	 * @param Loader $loader Hook loader.
	 * @return void
	 */
	public function register( Loader $loader ) {
		$loader->add_action( 'admin_init', $this, 'register_setting' );
	}

	/**
	 * Registers the option with the Settings API.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_setting() {
		register_setting(
			'dinoai_audit_settings_group',
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::defaults(),
				'show_in_rest'      => false,
			)
		);
	}

	/**
	 * Default settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults() {
		return array(
			'provider'            => 'openai',
			'api_key'             => '',
			'model'               => 'gpt-4o-mini',
			'tone'                => 'default',
			'custom_instructions' => '',
			'modules'             => array(
				'content' => true,
				'seo'     => true,
				'images'  => true,
				'audit'   => true,
			),
		);
	}

	/**
	 * Allowed writing tone slugs.
	 *
	 * @since 1.1.0
	 *
	 * @return array<string, string> Slug => label.
	 */
	public static function tone_options() {
		return array(
			'default'      => __( 'Default', 'dinoai-audit' ),
			'professional' => __( 'Professional', 'dinoai-audit' ),
			'casual'       => __( 'Casual', 'dinoai-audit' ),
			'friendly'     => __( 'Friendly', 'dinoai-audit' ),
			'concise'      => __( 'Concise', 'dinoai-audit' ),
		);
	}

	/**
	 * Instruction text for a tone slug (empty for default).
	 *
	 * @since 1.1.0
	 *
	 * @param string $tone Tone slug.
	 * @return string
	 */
	public static function tone_instruction( $tone ) {
		$map = array(
			'professional' => 'Write in a professional, clear tone suitable for a business website.',
			'casual'       => 'Write in a casual, conversational tone.',
			'friendly'     => 'Write in a warm, friendly, approachable tone.',
			'concise'      => 'Prefer short sentences and concise wording. Avoid filler.',
		);

		$tone = sanitize_key( $tone );

		return isset( $map[ $tone ] ) ? $map[ $tone ] : '';
	}

	/**
	 * Returns all settings merged with defaults.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>
	 */
	public function all() {
		$stored = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return wp_parse_args( $stored, self::defaults() );
	}

	/**
	 * Returns a single setting.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $fallback Default value.
	 * @return mixed
	 */
	public function get( $key, $fallback = null ) {
		$settings = $this->all();

		if ( array_key_exists( $key, $settings ) ) {
			return $settings[ $key ];
		}

		return $fallback;
	}

	/**
	 * Whether an AI provider is configured.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_configured() {
		$key = $this->get( 'api_key', '' );
		return is_string( $key ) && '' !== trim( $key );
	}

	/**
	 * Whether a module is enabled.
	 *
	 * @since 1.0.0
	 *
	 * @param string $module Module key.
	 * @return bool
	 */
	public function is_module_enabled( $module ) {
		$modules = $this->get( 'modules', array() );

		if ( ! is_array( $modules ) || ! array_key_exists( $module, $modules ) ) {
			return true;
		}

		return (bool) $modules[ $module ];
	}

	/**
	 * Returns the stored API key.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_api_key() {
		$key = $this->get( 'api_key', '' );
		return is_string( $key ) ? $key : '';
	}

	/**
	 * Returns a masked API key for display.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_masked_api_key() {
		$key = $this->get_api_key();

		if ( '' === $key ) {
			return '';
		}

		return str_repeat( '•', strlen( $key ) );
	}

	/**
	 * Public-safe settings for the admin UI. Never includes the raw API key.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>
	 */
	public function get_public_settings() {
		$all = $this->all();

		return array(
			'provider'    => sanitize_key( $all['provider'] ),
			'model'       => sanitize_text_field( $all['model'] ),
			'modules'     => $this->sanitize_modules( isset( $all['modules'] ) ? $all['modules'] : array() ),
			'configured'  => $this->is_configured(),
			'masked_key'  => $this->get_masked_api_key(),
			'has_api_key' => $this->is_configured(),
		);
	}

	/**
	 * Sanitizes settings before save.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $input Raw input.
	 * @return array<string, mixed>
	 */
	public function sanitize( $input ) {
		$current = $this->all();
		$clean   = self::defaults();

		if ( ! is_array( $input ) ) {
			return $current;
		}

		$allowed_providers = array_keys( AI_Manager::available_providers() );
		$provider          = isset( $input['provider'] ) ? sanitize_key( $input['provider'] ) : 'openai';
		$clean['provider'] = in_array( $provider, $allowed_providers, true ) ? $provider : 'openai';

		$allowed_models = AI_Manager::allowed_models( $clean['provider'] );
		$model          = isset( $input['model'] ) ? sanitize_text_field( $input['model'] ) : 'gpt-4o-mini';
		$clean['model'] = in_array( $model, $allowed_models, true ) ? $model : $allowed_models[0];

		$clean['modules'] = $this->sanitize_modules(
			isset( $input['modules'] ) && is_array( $input['modules'] ) ? $input['modules'] : array()
		);

		$tones = array_keys( self::tone_options() );
		$tone  = isset( $input['tone'] ) ? sanitize_key( $input['tone'] ) : 'default';
		$clean['tone'] = in_array( $tone, $tones, true ) ? $tone : 'default';

		$instructions = isset( $input['custom_instructions'] ) ? $input['custom_instructions'] : '';
		$clean['custom_instructions'] = $this->sanitize_custom_instructions( $instructions );

		$clean['api_key'] = $current['api_key'];

		if ( isset( $input['api_key'] ) && is_string( $input['api_key'] ) ) {
			$submitted = trim( $input['api_key'] );

			if ( '' === $submitted ) {
				$clean['api_key'] = '';
			} elseif ( ! $this->is_masked_api_key_value( $submitted ) ) {
				$clean['api_key'] = $this->sanitize_api_key( $submitted );
			}
		}

		return $clean;
	}

	/**
	 * Sanitizes site-wide custom AI instructions.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public function sanitize_custom_instructions( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$value = sanitize_textarea_field( $value );
		$value = trim( $value );

		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $value, 0, 1000 );
		}

		return substr( $value, 0, 1000 );
	}

	/**
	 * Whether a submitted API key is a masked placeholder, not a new key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value Submitted value.
	 * @return bool
	 */
	protected function is_masked_api_key_value( $value ) {
		return self::KEY_PLACEHOLDER === $value || false !== strpos( $value, '•' );
	}

	/**
	 * Sanitizes an API key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Raw key.
	 * @return string
	 */
	public function sanitize_api_key( $key ) {
		$key = trim( $key );
		$key = preg_replace( '/[^\x21-\x7E]/', '', $key );

		return is_string( $key ) ? substr( $key, 0, 512 ) : '';
	}

	/**
	 * Sanitizes module flags.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $modules Module flags.
	 * @return array<string, bool>
	 */
	public function sanitize_modules( $modules ) {
		$defaults = self::defaults()['modules'];
		$clean    = array();

		foreach ( $defaults as $key => $default ) {
			if ( ! isset( $modules[ $key ] ) ) {
				$clean[ $key ] = false;
				continue;
			}

			$value         = $modules[ $key ];
			$clean[ $key ] = ( true === $value || 1 === $value || '1' === $value || 'on' === $value );
		}

		return $clean;
	}
}
