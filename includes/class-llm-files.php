<?php
/**
 * Detects llms.txt and llms-full.txt for AI crawlers.
 *
 * @package WPDINO\DinoAIAudit
 */

namespace WPDINO\DinoAIAudit;

defined( 'ABSPATH' ) || exit;

/**
 * Light scan: document root file and public URL (for plugin-generated routes).
 *
 * @since 1.1.0
 */
class LLM_Files {

	/**
	 * Primary discovery file name.
	 *
	 * @var string
	 */
	const PRIMARY = 'llms.txt';

	/**
	 * Optional extended discovery file.
	 *
	 * @var string
	 */
	const EXTENDED = 'llms-full.txt';

	/**
	 * LLM discovery format specification URL (llmstxt.org v2).
	 *
	 * @var string
	 */
	const SPEC_URL = 'https://llmstxt.org/';

	/**
	 * Transient cache key.
	 *
	 * @var string
	 */
	const CACHE_KEY = 'dinoai_audit_llm_files';

	/**
	 * Scans for LLM discovery files.
	 *
	 * @since 1.1.0
	 *
	 * @param bool $use_cache Whether to read or store the transient cache.
	 * @return array<string, mixed>
	 */
	public static function scan( $use_cache = true ) {
		if ( $use_cache ) {
			$cached = get_transient( self::CACHE_KEY );

			if ( is_array( $cached ) ) {
				return $cached;
			}
		}

		$primary  = self::file_available( self::PRIMARY );
		$extended = self::file_available( self::EXTENDED );

		$result = array(
			'llms_txt'      => $primary,
			'llms_full_txt' => $extended,
			'has_primary'   => ! empty( $primary['available'] ),
			'has_any'       => ! empty( $primary['available'] ) || ! empty( $extended['available'] ),
			'checked_at'    => time(),
		);

		set_transient( self::CACHE_KEY, $result, HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Clears the scan cache (e.g. after creating a file).
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public static function flush_cache() {
		delete_transient( self::CACHE_KEY );
	}

	/**
	 * Whether a named file exists on disk or is served at the site URL.
	 *
	 * @since 1.1.0
	 *
	 * @param string $filename File name at web root (e.g. llms.txt).
	 * @return array{available: bool, source: string, url?: string}
	 */
	public static function file_available( $filename ) {
		$filename = sanitize_file_name( $filename );

		if ( '' === $filename ) {
			return array(
				'available' => false,
				'source'    => 'none',
			);
		}

		$path = trailingslashit( ABSPATH ) . $filename;

		if ( is_readable( $path ) && is_file( $path ) ) {
			return array(
				'available' => true,
				'source'    => 'file',
				'url'       => home_url( '/' . $filename ),
			);
		}

		/**
		 * Whether to HTTP-check the public URL when no physical file is found.
		 *
		 * @since 1.1.0
		 *
		 * @param bool   $check_remote Default true.
		 * @param string $filename     File name.
		 */
		$check_remote = apply_filters( 'dinoai_audit_llm_files_check_remote', true, $filename );

		if ( ! $check_remote ) {
			return array(
				'available' => false,
				'source'    => 'none',
				'url'       => home_url( '/' . $filename ),
			);
		}

		$url = home_url( '/' . $filename );

		$response = wp_remote_head(
			$url,
			array(
				'timeout'     => 5,
				'redirection' => 3,
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core HTTP filter (see wp-includes/class-wp-http-streams.php).
				'sslverify'   => apply_filters( 'https_local_ssl_verify', false ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'available' => false,
				'source'    => 'none',
				'url'       => $url,
			);
		}

		$code = (int) wp_remote_retrieve_response_code( $response );

		if ( $code >= 200 && $code < 300 ) {
			return array(
				'available' => true,
				'source'    => 'url',
				'url'       => $url,
			);
		}

		return array(
			'available' => false,
			'source'    => 'none',
			'url'       => $url,
		);
	}
}
