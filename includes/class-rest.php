<?php
/**
 * REST API.
 *
 * @package WPDINO\DinoAIAudit
 */

namespace WPDINO\DinoAIAudit;

defined( 'ABSPATH' ) || exit;

/**
 * Registers /dinoai-audit/v1/ routes.
 *
 * @since 1.0.0
 */
class REST {

	/**
	 * Namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'dinoai-audit/v1';

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
		$loader->add_action( 'rest_api_init', $this, 'register_routes' );
	}

	/**
	 * Registers REST routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/generate',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'generate' ),
				'permission_callback' => array( $this, 'can_generate' ),
				'args'                => array(
					'prompt_key' => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'input'      => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => array( $this, 'sanitize_input' ),
						'default'           => '',
					),
					'post_id'    => array(
						'type'              => 'integer',
						'required'          => false,
						'sanitize_callback' => 'absint',
						'default'           => 0,
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/image-alt',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'image_alt' ),
				'permission_callback' => array( $this, 'can_edit_attachment' ),
				'args'                => array(
					'attachment_id' => array(
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/image-alt/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'save_image_alt' ),
				'permission_callback' => array( $this, 'can_edit_attachment' ),
				'args'                => array(
					'attachment_id' => array(
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'alt_text'      => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/audit',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'audit' ),
				'permission_callback' => array( $this, 'can_manage' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/settings',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_settings' ),
				'permission_callback' => array( $this, 'can_manage' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/test-connection',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'test_connection' ),
				'permission_callback' => array( $this, 'can_manage' ),
			)
		);
	}

	/**
	 * Handles generate requests.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function generate( \WP_REST_Request $request ) {
		$prompt_key = (string) $request->get_param( 'prompt_key' );
		$post_id    = (int) $request->get_param( 'post_id' );
		$input      = (string) $request->get_param( 'input' );

		if ( ! in_array( $prompt_key, $this->plugin->prompts()->keys(), true ) ) {
			return $this->error(
				'dinoai_audit_unknown_prompt',
				__( 'DinoAI Audit could not complete the request.', 'dinoai-audit' ),
				400
			);
		}

		if ( 0 === strpos( $prompt_key, 'seo.' ) && $post_id ) {
			$post_input = $this->post_text( $post_id );

			if ( '' === $input ) {
				$input = $post_input;
			}
		}

		if ( '' === trim( wp_strip_all_tags( $input ) ) ) {
			return $this->error(
				'dinoai_audit_empty_input',
				__( 'Select or provide some content first.', 'dinoai-audit' ),
				400
			);
		}

		$context = $this->build_context( $post_id );
		$result  = $this->plugin->ai()->generate( $prompt_key, $input, $context );

		if ( is_wp_error( $result ) ) {
			return $this->from_wp_error( $result );
		}

		return rest_ensure_response(
			array(
				'success'    => true,
				'text'       => $result['text'],
				'structured' => $result['structured'],
			)
		);
	}

	/**
	 * Generates alt text for an attachment.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function image_alt( \WP_REST_Request $request ) {
		$attachment_id = (int) $request->get_param( 'attachment_id' );
		$images        = $this->plugin->module( 'images' );

		if ( ! $images instanceof Modules\Images ) {
			return $this->error(
				'dinoai_audit_module_disabled',
				__( 'The image module is disabled.', 'dinoai-audit' ),
				400
			);
		}

		$result = $images->generate_alt( $attachment_id );

		if ( is_wp_error( $result ) ) {
			return $this->from_wp_error( $result );
		}

		return rest_ensure_response(
			array(
				'success'  => true,
				'alt_text' => $result['text'],
			)
		);
	}

	/**
	 * Saves confirmed alt text.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function save_image_alt( \WP_REST_Request $request ) {
		$attachment_id = (int) $request->get_param( 'attachment_id' );
		$alt_text      = (string) $request->get_param( 'alt_text' );

		if ( 'attachment' !== get_post_type( $attachment_id ) ) {
			return $this->error(
				'dinoai_audit_invalid_attachment',
				__( 'The selected file is not a valid image.', 'dinoai-audit' ),
				400
			);
		}

		$alt_text = $this->plugin->ai()->sanitize_plain_text( $alt_text );

		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );

		return rest_ensure_response(
			array(
				'success'  => true,
				'alt_text' => $alt_text,
			)
		);
	}

	/**
	 * Runs the site audit.
	 *
	 * @since 1.0.0
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function audit() {
		$audit = $this->plugin->module( 'audit' );

		if ( ! $audit instanceof Modules\Site_Audit ) {
			return $this->error(
				'dinoai_audit_module_disabled',
				__( 'The audit module is disabled.', 'dinoai-audit' ),
				400
			);
		}

		$result = $audit->run();

		update_option( 'dinoai_audit_last_audit', $result, false );

		if ( isset( $result['issues'] ) && is_array( $result['issues'] ) ) {
			$result['issues'] = $audit->prepare_issues( $result['issues'] );
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Public-safe settings. Never returns the API key.
	 *
	 * @since 1.0.0
	 *
	 * @return \WP_REST_Response
	 */
	public function get_settings() {
		return rest_ensure_response( $this->plugin->settings()->get_public_settings() );
	}

	/**
	 * Tests the AI connection.
	 *
	 * @since 1.0.0
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function test_connection() {
		$result = $this->plugin->ai()->test_connection();

		if ( is_wp_error( $result ) ) {
			return $this->from_wp_error( $result );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Connection successful.', 'dinoai-audit' ),
			)
		);
	}

	/**
	 * Permission: generate content for a post the user can edit.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return bool|\WP_Error
	 */
	public function can_generate( \WP_REST_Request $request ) {
		if ( ! is_user_logged_in() ) {
			return $this->error( 'dinoai_audit_unauthenticated', __( 'You must be logged in to use DinoAI Audit.', 'dinoai-audit' ), 401 );
		}

		$post_id = (int) $request->get_param( 'post_id' );

		if ( $post_id ) {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $this->error( 'dinoai_audit_forbidden', __( 'You do not have permission to use DinoAI Audit on this content.', 'dinoai-audit' ), 403 );
			}

			return true;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return $this->error( 'dinoai_audit_forbidden', __( 'You do not have permission to use DinoAI Audit.', 'dinoai-audit' ), 403 );
		}

		return true;
	}

	/**
	 * Permission: edit the given attachment.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return bool|\WP_Error
	 */
	public function can_edit_attachment( \WP_REST_Request $request ) {
		if ( ! is_user_logged_in() ) {
			return $this->error( 'dinoai_audit_unauthenticated', __( 'You must be logged in to use DinoAI Audit.', 'dinoai-audit' ), 401 );
		}

		$attachment_id = (int) $request->get_param( 'attachment_id' );

		if ( ! $attachment_id || ! current_user_can( 'edit_post', $attachment_id ) ) {
			return $this->error( 'dinoai_audit_forbidden', __( 'You do not have permission to edit this image.', 'dinoai-audit' ), 403 );
		}

		if ( ! current_user_can( 'upload_files' ) ) {
			return $this->error( 'dinoai_audit_forbidden', __( 'You do not have permission to edit this image.', 'dinoai-audit' ), 403 );
		}

		return true;
	}

	/**
	 * Permission: manage plugin settings and audits.
	 *
	 * @since 1.0.0
	 *
	 * @return bool|\WP_Error
	 */
	public function can_manage() {
		if ( ! is_user_logged_in() ) {
			return $this->error( 'dinoai_audit_unauthenticated', __( 'You must be logged in to use DinoAI Audit.', 'dinoai-audit' ), 401 );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->error( 'dinoai_audit_forbidden', __( 'You do not have permission to manage DinoAI Audit.', 'dinoai-audit' ), 403 );
		}

		return true;
	}

	/**
	 * Sanitizes user-supplied prompt input.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public function sanitize_input( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$value = wp_kses_post( $value );

		if ( strlen( $value ) > 20000 ) {
			$value = substr( $value, 0, 20000 );
		}

		return $value;
	}

	/**
	 * Builds limited post context. Does not send comments, users, or secrets.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Post ID.
	 * @return array<string, string>
	 */
	protected function build_context( $post_id ) {
		$context = array(
			'title'  => '',
			'locale' => get_locale(),
		);

		if ( $post_id ) {
			$post = get_post( $post_id );

			if ( $post instanceof \WP_Post ) {
				$context['title'] = get_the_title( $post );
			}
		}

		return $context;
	}

	/**
	 * Extracts a reasonable amount of post text.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	protected function post_text( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post instanceof \WP_Post ) {
			return '';
		}

		$title   = get_the_title( $post );
		$content = wp_strip_all_tags( $post->post_content );
		$content = preg_replace( '/\s+/', ' ', $content );
		$content = is_string( $content ) ? trim( $content ) : '';

		if ( strlen( $content ) > 8000 ) {
			$content = substr( $content, 0, 8000 );
		}

		return trim( $title . "\n\n" . $content );
	}

	/**
	 * Converts a WP_Error into a REST error without leaking secrets.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Error $error Error.
	 * @return \WP_Error
	 */
	protected function from_wp_error( \WP_Error $error ) {
		$code   = $error->get_error_code();
		$status = 400;

		if ( in_array( $code, array( 'dinoai_audit_unauthenticated' ), true ) ) {
			$status = 401;
		} elseif ( in_array( $code, array( 'dinoai_audit_forbidden' ), true ) ) {
			$status = 403;
		} elseif ( in_array( $code, array( 'dinoai_audit_timeout', 'dinoai_audit_connection', 'dinoai_audit_provider_error', 'dinoai_audit_rate_limited' ), true ) ) {
			$status = 502;
		}

		$error->add_data( array( 'status' => $status ) );

		return $error;
	}

	/**
	 * Creates a REST error.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code    Error code.
	 * @param string $message Message.
	 * @param int    $status  HTTP status.
	 * @return \WP_Error
	 */
	protected function error( $code, $message, $status ) {
		return new \WP_Error( $code, $message, array( 'status' => $status ) );
	}
}
