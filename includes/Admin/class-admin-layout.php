<?php
/**
 * Shared WPDINO admin layout helpers.
 *
 * @package WPDINO\DinoAIAudit
 */

namespace WPDINO\DinoAIAudit\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Builds settings-page URLs and chrome used across DinoAI Audit admin screens.
 *
 * @since 1.0.0
 */
class Admin_Layout {

	/**
	 * Product page for DinoAI Audit Pro.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const PRO_URL = 'https://wpdino.com/plugins/dinoai-audit-pro/';

	/**
	 * Whether DinoAI Audit Pro is active.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_pro_active() {
		return defined( 'DINOAI_AUDIT_PRO_VERSION' );
	}

	/**
	 * Upgrade URL with admin UTM params (menu / plugins list).
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function go_pro_url() {
		return add_query_arg(
			array(
				'utm_source'   => 'wpadmin',
				'utm_medium'   => 'dinoai-audit',
				'utm_campaign' => 'go-pro-links',
			),
			self::PRO_URL
		);
	}

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
			'utm_campaign' => 'dinoai_audit_free',
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
			'seo'           => __( 'SEO', 'dinoai-audit' ),
			'content'       => __( 'Content', 'dinoai-audit' ),
			'accessibility' => __( 'Accessibility', 'dinoai-audit' ),
			'technical'     => __( 'Technical', 'dinoai-audit' ),
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
			'error'   => __( 'Error', 'dinoai-audit' ),
			'warning' => __( 'Warning', 'dinoai-audit' ),
			'notice'  => __( 'Notice', 'dinoai-audit' ),
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
				'label' => __( 'Looking good', 'dinoai-audit' ),
			);
		}

		if ( $score >= 60 ) {
			return array(
				'slug'  => 'fair',
				'label' => __( 'Room to improve', 'dinoai-audit' ),
			);
		}

		return array(
			'slug'  => 'poor',
			'label' => __( 'Needs attention', 'dinoai-audit' ),
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
