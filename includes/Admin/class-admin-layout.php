<?php
/**
 * Shared WPDINO admin layout helpers.
 *
 * @package WPDINO\DinoAI
 */

namespace WPDINO\DinoAI\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Builds settings-page URLs and chrome used across DinoAI admin screens.
 *
 * @since 1.0.0
 */
class Admin_Layout {

	/**
	 * Adds UTM parameters to a WPDINO URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url     Destination URL.
	 * @param string $content utm_content identifier.
	 * @return string
	 */
	public static function utm_url( $url, $content = '' ) {
		$params = array(
			'utm_source'   => 'plugin',
			'utm_medium'   => 'settings_page',
			'utm_campaign' => 'dinoai_free',
		);

		if ( '' !== $content ) {
			$params['utm_content'] = $content;
		}

		return add_query_arg( $params, $url );
	}

	/**
	 * Dashicon for an audit category.
	 *
	 * @since 1.0.0
	 *
	 * @param string $category Category slug.
	 * @return string
	 */
	public static function category_icon( $category ) {
		$map = array(
			'seo'           => 'dashicons-search',
			'content'       => 'dashicons-edit',
			'accessibility' => 'dashicons-universal-access-alt',
			'technical'     => 'dashicons-admin-tools',
		);

		return isset( $map[ $category ] ) ? $map[ $category ] : 'dashicons-flag';
	}

	/**
	 * Human label for an audit category.
	 *
	 * @since 1.0.0
	 *
	 * @param string $category Category slug.
	 * @return string
	 */
	public static function category_label( $category ) {
		$labels = array(
			'seo'           => __( 'SEO', 'dinoai' ),
			'content'       => __( 'Content', 'dinoai' ),
			'accessibility' => __( 'Accessibility', 'dinoai' ),
			'technical'     => __( 'Technical', 'dinoai' ),
		);

		return isset( $labels[ $category ] ) ? $labels[ $category ] : ucfirst( $category );
	}

	/**
	 * Human label for an issue severity.
	 *
	 * @since 1.0.0
	 *
	 * @param string $severity Severity slug.
	 * @return string
	 */
	public static function severity_label( $severity ) {
		$labels = array(
			'error'   => __( 'Error', 'dinoai' ),
			'warning' => __( 'Warning', 'dinoai' ),
			'notice'  => __( 'Notice', 'dinoai' ),
		);

		return isset( $labels[ $severity ] ) ? $labels[ $severity ] : ucfirst( $severity );
	}

	/**
	 * Score band used for color and copy. Never color-only.
	 *
	 * @since 1.0.0
	 *
	 * @param int $score Score 0–100.
	 * @return array{slug:string,label:string}
	 */
	public static function score_band( $score ) {
		$score = (int) $score;

		if ( $score >= 80 ) {
			return array(
				'slug'  => 'good',
				'label' => __( 'Looking good', 'dinoai' ),
			);
		}

		if ( $score >= 60 ) {
			return array(
				'slug'  => 'fair',
				'label' => __( 'Room to improve', 'dinoai' ),
			);
		}

		return array(
			'slug'  => 'poor',
			'label' => __( 'Needs attention', 'dinoai' ),
		);
	}

	/**
	 * Dashicon for a severity level.
	 *
	 * @since 1.0.0
	 *
	 * @param string $severity Severity slug.
	 * @return string
	 */
	public static function severity_icon( $severity ) {
		$map = array(
			'error'   => 'dashicons-warning',
			'warning' => 'dashicons-flag',
			'notice'  => 'dashicons-info',
		);

		return isset( $map[ $severity ] ) ? $map[ $severity ] : 'dashicons-info';
	}
}
