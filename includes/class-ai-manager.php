<?php
/**
 * AI manager.
 *
 * @package WPDINO\DinoAIAudit
 */

namespace WPDINO\DinoAIAudit;

defined( 'ABSPATH' ) || exit;

/**
 * Resolves providers, normalizes responses, and records usage.
 *
 * @since 1.0.0
 */
class AI_Manager {

	/**
	 * Settings.
	 *
	 * @var Settings
	 */
	protected $settings;

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
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Settings       $settings Settings.
	 * @param Prompt_Manager $prompts  Prompts.
	 * @param Usage          $usage    Usage tracker.
	 */
	public function __construct( Settings $settings, Prompt_Manager $prompts, Usage $usage ) {
		$this->settings = $settings;
		$this->prompts  = $prompts;
		$this->usage    = $usage;
	}

	/**
	 * Available providers. OpenAI is the only bundled provider.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, string>
	 */
	public static function available_providers() {
		$providers = array(
			'openai' => __( 'OpenAI', 'dinoai-audit' ),
		);

		/**
		 * Filters available AI providers.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, string> $providers Slug => label.
		 */
		return apply_filters( 'dinoai_audit_ai_providers', $providers );
	}

	/**
	 * Allowed models for a provider.
	 *
	 * @since 1.0.0
	 *
	 * @param string $provider Provider slug.
	 * @return string[]
	 */
	public static function allowed_models( $provider = 'openai' ) {
		$models = array(
			'gpt-4o-mini',
			'gpt-4o',
			'gpt-4.1-mini',
			'gpt-4.1',
		);

		/**
		 * Filters selectable models.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $models   Model ids.
		 * @param string   $provider Provider slug.
		 */
		$models = apply_filters( 'dinoai_audit_allowed_models', $models, $provider );

		return array_values( array_filter( $models, 'is_string' ) );
	}

	/**
	 * Human-readable label for a model id.
	 *
	 * @since 1.0.0
	 *
	 * @param string $model Model id.
	 * @return string
	 */
	public static function model_label( $model ) {
		$labels = array(
			'gpt-4o-mini'  => __( 'GPT-4o mini (Recommended)', 'dinoai-audit' ),
			'gpt-4o'       => __( 'GPT-4o', 'dinoai-audit' ),
			'gpt-4.1-mini' => __( 'GPT-4.1 mini', 'dinoai-audit' ),
			'gpt-4.1'      => __( 'GPT-4.1', 'dinoai-audit' ),
		);

		$label = isset( $labels[ $model ] ) ? $labels[ $model ] : $model;

		/**
		 * Filters the human-readable label for a model id.
		 *
		 * @since 1.0.0
		 *
		 * @param string $label Display label.
		 * @param string $model Model id.
		 */
		return apply_filters( 'dinoai_audit_model_label', $label, $model );
	}

	/**
	 * Whether the current configuration is ready.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_configured() {
		return $this->settings->is_configured();
	}

	/**
	 * Whether the current provider/model can analyze images.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function supports_vision() {
		$provider = $this->get_provider();

		if ( is_wp_error( $provider ) ) {
			return false;
		}

		return $provider->supports_vision();
	}

	/**
	 * Builds the configured provider.
	 *
	 * @since 1.0.0
	 *
	 * @return AI\Provider_Interface|\WP_Error
	 */
	public function get_provider() {
		if ( ! $this->is_configured() ) {
			return new \WP_Error(
				'dinoai_audit_not_configured',
				__( 'No AI provider configured. Connect an AI provider in DinoAI Audit → Settings.', 'dinoai-audit' )
			);
		}

		$slug    = $this->settings->get( 'provider', 'openai' );
		$api_key = $this->settings->get_api_key();
		$model   = $this->settings->get( 'model', 'gpt-4o-mini' );

		$providers = array(
			'openai' => AI\OpenAI_Provider::class,
		);

		/**
		 * Filters provider class map.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, class-string> $providers Slug => class.
		 */
		$providers = apply_filters( 'dinoai_audit_ai_provider_classes', $providers );

		if ( ! isset( $providers[ $slug ] ) || ! class_exists( $providers[ $slug ] ) ) {
			return new \WP_Error(
				'dinoai_audit_unknown_provider',
				__( 'The selected AI provider is not available.', 'dinoai-audit' )
			);
		}

		$class_name = $providers[ $slug ];

		return new $class_name( $api_key, $model );
	}

	/**
	 * Runs a prompt key against the configured provider.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $prompt_key Prompt identifier.
	 * @param string               $input      User-selected content.
	 * @param array<string, mixed> $context    Extra context.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function generate( $prompt_key, $input, array $context = array() ) {
		$provider = $this->get_provider();

		if ( is_wp_error( $provider ) ) {
			return $provider;
		}

		$context['input'] = $input;
		$prompt           = $this->prompts->get_prompt( $prompt_key, $context );

		if ( is_wp_error( $prompt ) ) {
			return $prompt;
		}

		$module = $this->module_from_key( $prompt_key );

		if ( $this->prompts->is_structured( $prompt_key ) ) {
			$result = $provider->generate_structured( $prompt );
		} else {
			$result = $provider->generate_text( $prompt );
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$result = $this->normalize_result( $prompt_key, $result );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$this->usage->record(
			$module,
			$result['provider'],
			$result['model'],
			$result['tokens_in'],
			$result['tokens_out']
		);

		return $result;
	}

	/**
	 * Analyzes an image with the configured provider.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $prompt_key Prompt identifier.
	 * @param string               $image_ref  Data URL or remote URL.
	 * @param array<string, mixed> $context    Extra context.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function analyze_image( $prompt_key, $image_ref, array $context = array() ) {
		$provider = $this->get_provider();

		if ( is_wp_error( $provider ) ) {
			return $provider;
		}

		if ( ! $provider->supports_vision() ) {
			return new \WP_Error(
				'dinoai_audit_no_vision',
				__( 'The selected AI model cannot analyze images. Choose a vision-capable model such as gpt-4o-mini in DinoAI Audit settings.', 'dinoai-audit' )
			);
		}

		$prompt = $this->prompts->get_prompt( $prompt_key, $context );

		if ( is_wp_error( $prompt ) ) {
			return $prompt;
		}

		$result = $provider->analyze_image( $prompt, $image_ref );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$result['text'] = $this->sanitize_plain_text( $result['text'] );

		if ( '' === $result['text'] ) {
			return new \WP_Error(
				'dinoai_audit_empty_response',
				__( 'DinoAI Audit could not complete the request. The AI returned an empty response.', 'dinoai-audit' )
			);
		}

		$this->usage->record(
			'images',
			$result['provider'],
			$result['model'],
			$result['tokens_in'],
			$result['tokens_out']
		);

		return $result;
	}

	/**
	 * Tests the current provider connection.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|\WP_Error
	 */
	public function test_connection() {
		$provider = $this->get_provider();

		if ( is_wp_error( $provider ) ) {
			return $provider;
		}

		$result = $provider->test_connection();

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$this->usage->record(
			'test',
			$result['provider'],
			$result['model'],
			isset( $result['tokens_in'] ) ? $result['tokens_in'] : 0,
			isset( $result['tokens_out'] ) ? $result['tokens_out'] : 0
		);

		return $result;
	}

	/**
	 * Validates structured payloads for known prompt keys.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $prompt_key Prompt identifier.
	 * @param array<string, mixed> $data       Decoded JSON.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function validate_structured( $prompt_key, array $data ) {
		switch ( $prompt_key ) {
			case 'seo.title':
				if ( empty( $data['title'] ) || ! is_string( $data['title'] ) ) {
					return new \WP_Error( 'dinoai_audit_invalid_json', __( 'DinoAI Audit received an unexpected response. Please try again.', 'dinoai-audit' ) );
				}

				return array(
					'title' => $this->sanitize_plain_text( $data['title'] ),
				);

			case 'seo.meta_description':
				if ( empty( $data['meta_description'] ) || ! is_string( $data['meta_description'] ) ) {
					return new \WP_Error( 'dinoai_audit_invalid_json', __( 'DinoAI Audit received an unexpected response. Please try again.', 'dinoai-audit' ) );
				}

				return array(
					'meta_description' => $this->sanitize_plain_text( $data['meta_description'] ),
				);

			case 'seo.keywords':
				if ( empty( $data['keywords'] ) || ! is_array( $data['keywords'] ) ) {
					return new \WP_Error( 'dinoai_audit_invalid_json', __( 'DinoAI Audit received an unexpected response. Please try again.', 'dinoai-audit' ) );
				}

				$keywords = array();

				foreach ( $data['keywords'] as $keyword ) {
					if ( ! is_string( $keyword ) ) {
						continue;
					}

					$clean = $this->sanitize_plain_text( $keyword );

					if ( '' !== $clean ) {
						$keywords[] = $clean;
					}

					if ( count( $keywords ) >= 8 ) {
						break;
					}
				}

				if ( empty( $keywords ) ) {
					return new \WP_Error( 'dinoai_audit_invalid_json', __( 'DinoAI Audit received an unexpected response. Please try again.', 'dinoai-audit' ) );
				}

				return array( 'keywords' => $keywords );

			case 'seo.analyze':
				if ( empty( $data['suggestions'] ) || ! is_array( $data['suggestions'] ) ) {
					return new \WP_Error( 'dinoai_audit_invalid_json', __( 'DinoAI Audit received an unexpected response. Please try again.', 'dinoai-audit' ) );
				}

				$suggestions = array();

				foreach ( $data['suggestions'] as $item ) {
					if ( ! is_array( $item ) || empty( $item['message'] ) || ! is_string( $item['message'] ) ) {
						continue;
					}

					$severity = isset( $item['severity'] ) ? sanitize_key( $item['severity'] ) : 'notice';

					if ( ! in_array( $severity, array( 'notice', 'warning', 'error' ), true ) ) {
						$severity = 'notice';
					}

					$suggestions[] = array(
						'severity' => $severity,
						'message'  => $this->sanitize_plain_text( $item['message'] ),
					);

					if ( count( $suggestions ) >= 10 ) {
						break;
					}
				}

				if ( empty( $suggestions ) ) {
					return new \WP_Error( 'dinoai_audit_invalid_json', __( 'DinoAI Audit received an unexpected response. Please try again.', 'dinoai-audit' ) );
				}

				return array( 'suggestions' => $suggestions );
		}

		/**
		 * Filters structured AI payloads for unknown or extended prompt keys.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, mixed>|\WP_Error $data       Validated data.
		 * @param string                         $prompt_key Prompt identifier.
		 */
		return apply_filters( 'dinoai_audit_validate_structured', $data, $prompt_key );
	}

	/**
	 * Normalizes a provider result.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $prompt_key Prompt identifier.
	 * @param array<string, mixed> $result     Provider result.
	 * @return array<string, mixed>|\WP_Error
	 */
	protected function normalize_result( $prompt_key, array $result ) {
		if ( $this->prompts->is_structured( $prompt_key ) ) {
			if ( empty( $result['structured'] ) || ! is_array( $result['structured'] ) ) {
				return new \WP_Error(
					'dinoai_audit_invalid_json',
					__( 'DinoAI Audit received an unexpected response. Please try again.', 'dinoai-audit' )
				);
			}

			$validated = $this->validate_structured( $prompt_key, $result['structured'] );

			if ( is_wp_error( $validated ) ) {
				return $validated;
			}

			$result['structured'] = $validated;
			$result['text']       = $this->text_from_structured( $prompt_key, $validated );
		} else {
			$result['text'] = $this->sanitize_content( $result['text'] );
		}

		return $result;
	}

	/**
	 * Builds display text from structured data.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $prompt_key Prompt identifier.
	 * @param array<string, mixed> $data       Structured data.
	 * @return string
	 */
	protected function text_from_structured( $prompt_key, array $data ) {
		if ( isset( $data['title'] ) ) {
			return $data['title'];
		}

		if ( isset( $data['meta_description'] ) ) {
			return $data['meta_description'];
		}

		if ( isset( $data['keywords'] ) && is_array( $data['keywords'] ) ) {
			return implode( ', ', $data['keywords'] );
		}

		if ( isset( $data['suggestions'] ) && is_array( $data['suggestions'] ) ) {
			$lines = array();

			foreach ( $data['suggestions'] as $item ) {
				if ( isset( $item['message'] ) ) {
					$lines[] = $item['message'];
				}
			}

			return implode( "\n", $lines );
		}

		unset( $prompt_key );

		return '';
	}

	/**
	 * Module key from a prompt key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $prompt_key Prompt identifier.
	 * @return string
	 */
	protected function module_from_key( $prompt_key ) {
		$parts = explode( '.', $prompt_key );
		$mod   = isset( $parts[0] ) ? $parts[0] : 'content';

		if ( 'connection' === $mod ) {
			return 'test';
		}

		return $mod;
	}

	/**
	 * Sanitizes AI content that may include HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text Raw text.
	 * @return string
	 */
	public function sanitize_content( $text ) {
		$text = $this->strip_unsafe( $text );

		return wp_kses_post( $text );
	}

	/**
	 * Sanitizes plain text AI output.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text Raw text.
	 * @return string
	 */
	public function sanitize_plain_text( $text ) {
		$text = $this->strip_unsafe( $text );
		$text = wp_strip_all_tags( $text );

		return trim( sanitize_text_field( $text ) );
	}

	/**
	 * Strips null bytes and trims.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text Raw text.
	 * @return string
	 */
	protected function strip_unsafe( $text ) {
		$text = (string) $text;
		$text = str_replace( "\0", '', $text );

		return trim( $text );
	}
}
