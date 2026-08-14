<?php
/**
 * Privacy policy content.
 *
 * @package WPDINO\DinoAIAudit
 */

namespace WPDINO\DinoAIAudit\Admin;

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAIAudit\Loader;

/**
 * Explains what data is sent to the configured AI provider.
 *
 * @since 1.0.0
 */
class Privacy {

	/**
	 * Registers hooks.
	 *
	 * @since 1.0.0
	 *
	 * @param Loader $loader Hook loader.
	 * @return void
	 */
	public function register( Loader $loader ) {
		$loader->add_action( 'admin_init', $this, 'add_privacy_content' );
	}

	/**
	 * Adds suggested privacy policy text.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_privacy_content() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$content  = '<p>' . esc_html__( 'DinoAI Audit sends content you choose (selected text, post content used for SEO suggestions, or images used for alt text) to the AI provider configured in the plugin settings. This is OpenAI when an API key is saved.', 'dinoai-audit' ) . '</p>';
		$content .= '<p>' . esc_html__( 'Requests are made only when a logged-in user with the required capability starts an action. DinoAI Audit does not send content automatically in the background.', 'dinoai-audit' ) . '</p>';
		$content .= '<p>' . esc_html__( 'The plugin stores your API key and basic usage counts (request totals, module, model, approximate token usage, and timestamps) on this site. It does not store full prompts or full AI responses.', 'dinoai-audit' ) . '</p>';
		$content .= '<p>' . esc_html__( 'The AI provider’s own privacy policy and terms apply to data they receive. Review OpenAI’s privacy policy if that provider is used.', 'dinoai-audit' ) . '</p>';

		wp_add_privacy_policy_content( 'DinoAI Audit', wp_kses_post( $content ) );
	}
}
