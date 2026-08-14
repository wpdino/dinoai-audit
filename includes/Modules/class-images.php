<?php
/**
 * Image alt text module.
 *
 * @package WPDINO\DinoAI
 */

namespace WPDINO\DinoAI\Modules;

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAI\Loader;
use WPDINO\DinoAI\Plugin;

/**
 * Generates accessibility-focused alt text.
 *
 * @since 1.0.0
 */
class Images {

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
		$loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_media' );
	}

	/**
	 * Enqueues media library integration.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook Current admin page.
	 * @return void
	 */
	public function enqueue_media( $hook ) {
		$screens = array( 'upload.php', 'post.php', 'post-new.php', 'media.php' );

		if ( ! in_array( $hook, $screens, true ) ) {
			return;
		}

		if ( ! current_user_can( 'upload_files' ) ) {
			return;
		}

		wp_enqueue_script(
			'dinoai-media',
			DINOAI_URL . 'admin/js/media.js',
			array( 'jquery', 'media-views', 'wp-i18n', 'wp-api-fetch' ),
			DINOAI_VERSION,
			true
		);

		wp_set_script_translations( 'dinoai-media', 'dinoai', DINOAI_PATH . 'languages' );

		wp_add_inline_script(
			'dinoai-media',
			'window.dinoaiMedia = ' . wp_json_encode(
				array(
					'configured'     => $this->plugin->settings()->is_configured(),
					'supportsVision' => $this->plugin->ai()->supports_vision(),
					'settingsUrl'    => admin_url( 'admin.php?page=dinoai-settings' ),
					'restUrl'        => rest_url( 'dinoai/v1/' ),
				)
			) . ';',
			'before'
		);
	}

	/**
	 * Generates alt text for an attachment.
	 *
	 * @since 1.0.0
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function generate_alt( $attachment_id ) {
		$post = get_post( $attachment_id );

		if ( ! $post instanceof \WP_Post || 'attachment' !== $post->post_type ) {
			return new \WP_Error(
				'dinoai_invalid_attachment',
				__( 'The selected file is not a valid image.', 'dinoai' )
			);
		}

		if ( 0 !== strpos( (string) $post->post_mime_type, 'image/' ) ) {
			return new \WP_Error(
				'dinoai_invalid_attachment',
				__( 'The selected file is not a valid image.', 'dinoai' )
			);
		}

		if ( ! $this->plugin->ai()->supports_vision() ) {
			return new \WP_Error(
				'dinoai_no_vision',
				__( 'The selected AI model cannot analyze images. Choose a vision-capable model such as gpt-4o-mini in DinoAI settings.', 'dinoai' )
			);
		}

		$image_ref = $this->prepare_image( $attachment_id );

		if ( is_wp_error( $image_ref ) ) {
			return $image_ref;
		}

		$caption = get_the_excerpt( $post );
		$context = array(
			'input' => $caption ? sprintf(
				/* translators: %s: image caption */
				__( 'Image caption: %s', 'dinoai' ),
				$caption
			) : '',
		);

		return $this->plugin->ai()->analyze_image( 'image.alt_text', $image_ref, $context );
	}

	/**
	 * Prepares a resized data URL or remote URL for the provider.
	 *
	 * @since 1.0.0
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return string|\WP_Error
	 */
	protected function prepare_image( $attachment_id ) {
		$file = get_attached_file( $attachment_id );

		if ( $file && file_exists( $file ) && is_readable( $file ) ) {
			$editor = wp_get_image_editor( $file );

			if ( ! is_wp_error( $editor ) ) {
				$size = $editor->get_size();

				if ( is_array( $size ) && ( $size['width'] > 1024 || $size['height'] > 1024 ) ) {
					$editor->resize( 1024, 1024, false );
				}

				$saved = $editor->save();

				if ( ! is_wp_error( $saved ) && ! empty( $saved['path'] ) && is_readable( $saved['path'] ) ) {
					$bytes = file_get_contents( $saved['path'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local file.

					if ( $saved['path'] !== $file ) {
						wp_delete_file( $saved['path'] );
					}

					if ( false !== $bytes && strlen( $bytes ) < 4000000 ) {
						$mime = isset( $saved['mime-type'] ) ? $saved['mime-type'] : 'image/jpeg';
						return 'data:' . $mime . ';base64,' . base64_encode( $bytes ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- required for vision payloads.
					}
				}
			}

			$bytes = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local file.

			if ( false !== $bytes && strlen( $bytes ) < 4000000 ) {
				$mime = get_post_mime_type( $attachment_id );
				$mime = $mime ? $mime : 'image/jpeg';
				return 'data:' . $mime . ';base64,' . base64_encode( $bytes ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- required for vision payloads.
			}
		}

		$url = wp_get_attachment_image_url( $attachment_id, 'large' );

		if ( ! $url ) {
			$url = wp_get_attachment_url( $attachment_id );
		}

		if ( ! $url ) {
			return new \WP_Error(
				'dinoai_missing_image',
				__( 'DinoAI could not read this image.', 'dinoai' )
			);
		}

		return esc_url_raw( $url );
	}
}
