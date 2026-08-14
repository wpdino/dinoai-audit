<?php
/**
 * OpenAI provider.
 *
 * @package WPDINO\DinoAI
 */

namespace WPDINO\DinoAI\AI;

defined( 'ABSPATH' ) || exit;

/**
 * Talks to the OpenAI Chat Completions API.
 *
 * @since 1.0.0
 */
class OpenAI_Provider implements Provider_Interface {

	/**
	 * API endpoint.
	 *
	 * @var string
	 */
	const ENDPOINT = 'https://api.openai.com/v1/chat/completions';

	/**
	 * API key.
	 *
	 * @var string
	 */
	protected $api_key;

	/**
	 * Model id.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $api_key API key.
	 * @param string $model   Model id.
	 */
	public function __construct( $api_key, $model ) {
		$this->api_key = (string) $api_key;
		$this->model   = (string) $model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_slug() {
		return 'openai';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label() {
		return __( 'OpenAI', 'dinoai' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function supports_vision() {
		$vision_models = array(
			'gpt-4o',
			'gpt-4o-mini',
			'gpt-4.1',
			'gpt-4.1-mini',
			'gpt-4.1-nano',
			'gpt-4-turbo',
		);

		/**
		 * Filters OpenAI models treated as vision-capable.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $vision_models Model ids.
		 * @param string   $model         Current model.
		 */
		$vision_models = apply_filters( 'dinoai_openai_vision_models', $vision_models, $this->model );

		return in_array( $this->model, $vision_models, true );
	}

	/**
	 * Generates plain text.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $prompt  Prompt text.
	 * @param array<string, mixed> $options Request options.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function generate_text( $prompt, array $options = array() ) {
		$messages = array(
			array(
				'role'    => 'user',
				'content' => $prompt,
			),
		);

		return $this->request( $messages, $options );
	}

	/**
	 * Generates structured JSON.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $prompt  Prompt text.
	 * @param array<string, mixed> $options Request options.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function generate_structured( $prompt, array $options = array() ) {
		$options['json'] = true;

		$result = $this->generate_text( $prompt, $options );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$parsed = self::parse_json( $result['text'] );

		if ( is_wp_error( $parsed ) ) {
			return $parsed;
		}

		$result['structured'] = $parsed;

		return $result;
	}

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
	public function analyze_image( $prompt, $image_ref, array $options = array() ) {
		if ( ! $this->supports_vision() ) {
			return new \WP_Error(
				'dinoai_no_vision',
				__( 'The selected AI model cannot analyze images. Choose a vision-capable model such as gpt-4o-mini in DinoAI settings.', 'dinoai' )
			);
		}

		$messages = array(
			array(
				'role'    => 'user',
				'content' => array(
					array(
						'type' => 'text',
						'text' => $prompt,
					),
					array(
						'type'      => 'image_url',
						'image_url' => array(
							'url' => $image_ref,
						),
					),
				),
			),
		);

		$options['timeout'] = isset( $options['timeout'] ) ? $options['timeout'] : 90;

		return $this->request( $messages, $options );
	}

	/**
	 * {@inheritdoc}
	 */
	public function test_connection() {
		return $this->generate_text(
			'Reply with the single word OK.',
			array(
				'timeout'     => 20,
				'max_tokens'  => 16,
				'temperature' => 0,
			)
		);
	}

	/**
	 * Sends a chat completion request.
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, array<string, mixed>> $messages Chat messages.
	 * @param array<string, mixed>             $options  Request options.
	 * @return array<string, mixed>|\WP_Error
	 */
	protected function request( array $messages, array $options = array() ) {
		if ( '' === $this->api_key ) {
			return new \WP_Error(
				'dinoai_not_configured',
				__( 'No AI provider configured. Connect an AI provider in DinoAI → Settings.', 'dinoai' )
			);
		}

		$body = array(
			'model'       => $this->model,
			'messages'    => $messages,
			'temperature' => isset( $options['temperature'] ) ? (float) $options['temperature'] : 0.4,
		);

		if ( isset( $options['max_tokens'] ) ) {
			$body['max_tokens'] = (int) $options['max_tokens'];
		}

		if ( ! empty( $options['json'] ) ) {
			$body['response_format'] = array( 'type' => 'json_object' );
		}

		$response = wp_remote_post(
			self::ENDPOINT,
			array(
				'timeout' => isset( $options['timeout'] ) ? (int) $options['timeout'] : 60,
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $this->connection_error( $response );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$raw  = wp_remote_retrieve_body( $response );
		$data = json_decode( $raw, true );

		if ( $code < 200 || $code >= 300 ) {
			return $this->http_error( $code, is_array( $data ) ? $data : array() );
		}

		if ( ! is_array( $data ) ) {
			return new \WP_Error(
				'dinoai_invalid_response',
				__( 'DinoAI could not complete the request. Please check your AI provider settings and try again.', 'dinoai' )
			);
		}

		$text = '';

		if ( isset( $data['choices'][0]['message']['content'] ) && is_string( $data['choices'][0]['message']['content'] ) ) {
			$text = trim( $data['choices'][0]['message']['content'] );
		}

		if ( '' === $text ) {
			return new \WP_Error(
				'dinoai_empty_response',
				__( 'DinoAI could not complete the request. The AI returned an empty response.', 'dinoai' )
			);
		}

		$tokens_in  = 0;
		$tokens_out = 0;

		if ( isset( $data['usage']['prompt_tokens'] ) ) {
			$tokens_in = (int) $data['usage']['prompt_tokens'];
		}

		if ( isset( $data['usage']['completion_tokens'] ) ) {
			$tokens_out = (int) $data['usage']['completion_tokens'];
		}

		return array(
			'text'       => $text,
			'structured' => null,
			'model'      => isset( $data['model'] ) ? sanitize_text_field( $data['model'] ) : $this->model,
			'provider'   => $this->get_slug(),
			'tokens_in'  => $tokens_in,
			'tokens_out' => $tokens_out,
		);
	}

	/**
	 * Maps transport errors to a safe user-facing message.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Error $error Transport error.
	 * @return \WP_Error
	 */
	protected function connection_error( \WP_Error $error ) {
		$code = $error->get_error_code();

		if ( in_array( $code, array( 'http_request_failed', 'http_request_not_executed' ), true ) ) {
			$message = $error->get_error_message();

			if ( is_string( $message ) && false !== stripos( $message, 'timed out' ) ) {
				return new \WP_Error(
					'dinoai_timeout',
					__( 'The AI request timed out. Please try again.', 'dinoai' )
				);
			}
		}

		return new \WP_Error(
			'dinoai_connection',
			__( 'DinoAI could not reach the AI provider. Please check your connection and try again.', 'dinoai' )
		);
	}

	/**
	 * Maps HTTP errors to safe messages. Never includes the API key.
	 *
	 * @since 1.0.0
	 *
	 * @param int                  $status HTTP status.
	 * @param array<string, mixed> $data   Decoded body.
	 * @return \WP_Error
	 */
	protected function http_error( $status, array $data ) {
		if ( 401 === $status ) {
			return new \WP_Error(
				'dinoai_invalid_key',
				__( 'The AI API key appears to be invalid. Please check DinoAI → Settings.', 'dinoai' )
			);
		}

		if ( 429 === $status ) {
			return new \WP_Error(
				'dinoai_rate_limited',
				__( 'The AI provider rate-limited this request. Please wait a moment and try again.', 'dinoai' )
			);
		}

		if ( 404 === $status ) {
			return new \WP_Error(
				'dinoai_unavailable_model',
				__( 'The selected AI model is unavailable. Choose a different model in DinoAI settings.', 'dinoai' )
			);
		}

		$type = '';

		if ( isset( $data['error']['type'] ) && is_string( $data['error']['type'] ) ) {
			$type = $data['error']['type'];
		}

		if ( 'insufficient_quota' === $type ) {
			return new \WP_Error(
				'dinoai_quota',
				__( 'The AI provider reported insufficient quota. Please check your OpenAI account.', 'dinoai' )
			);
		}

		return new \WP_Error(
			'dinoai_provider_error',
			__( 'DinoAI could not complete the request. Please check your AI provider settings and try again.', 'dinoai' )
		);
	}

	/**
	 * Parses JSON from an AI response.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text Raw text.
	 * @return array<string, mixed>|\WP_Error
	 */
	public static function parse_json( $text ) {
		$text = trim( (string) $text );

		if ( preg_match( '/```(?:json)?\s*(\{.*\})\s*```/s', $text, $matches ) ) {
			$text = $matches[1];
		}

		$decoded = json_decode( $text, true );

		if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
			return new \WP_Error(
				'dinoai_invalid_json',
				__( 'DinoAI received an unexpected response. Please try again.', 'dinoai' )
			);
		}

		return $decoded;
	}
}
