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

		$terms_url   = 'https://openai.com/policies/terms-of-use';
		$privacy_url = 'https://openai.com/policies/privacy-policy';
		$pricing_url = 'https://openai.com/pricing';

		$content  = '<p>' . esc_html__( 'DinoAI Audit does not collect, store, or share personal data by default. All data stays on this WordPress site unless you configure an OpenAI API key and a logged-in user with the required capability starts an action.', 'dinoai-audit' ) . '</p>';
		$content .= '<p><strong>' . esc_html__( 'What is sent to OpenAI', 'dinoai-audit' ) . '</strong></p>';
		$content .= '<ul>';
		$content .= '<li>' . esc_html__( 'Selected or pasted text when you run a content action (Improve, Rewrite, Shorten, Expand, or Summarize).', 'dinoai-audit' ) . '</li>';
		$content .= '<li>' . esc_html__( 'Post title and post content (plain text, truncated) when you generate an SEO title, meta description, or keyword ideas.', 'dinoai-audit' ) . '</li>';
		$content .= '<li>' . esc_html__( 'The selected image (as a data URL or image URL) and optional caption when you generate alt text.', 'dinoai-audit' ) . '</li>';
		$content .= '<li>' . esc_html__( 'A short test prompt when you click Test connection.', 'dinoai-audit' ) . '</li>';
		$content .= '<li>' . esc_html__( 'Your OpenAI API key, sent only to OpenAI as part of those API requests.', 'dinoai-audit' ) . '</li>';
		$content .= '</ul>';
		$content .= '<p>' . esc_html__( 'The basic site audit runs on this site and does not send post content to OpenAI.', 'dinoai-audit' ) . '</p>';
		$content .= '<p><strong>' . esc_html__( 'When data is sent', 'dinoai-audit' ) . '</strong></p>';
		$content .= '<p>' . esc_html__( 'Data is sent only when an API key is saved and a logged-in user starts an action or tests the connection. DinoAI Audit does not send content automatically in the background or from the public site.', 'dinoai-audit' ) . '</p>';
		$content .= '<p><strong>' . esc_html__( 'What this plugin stores', 'dinoai-audit' ) . '</strong></p>';
		$content .= '<p>' . esc_html__( 'The plugin stores your API key and basic usage counts (request totals, module, model, approximate token usage, and timestamps) on this site. It does not store full prompts or full AI responses.', 'dinoai-audit' ) . '</p>';
		$content .= '<p><strong>' . esc_html__( 'OpenAI terms and privacy', 'dinoai-audit' ) . '</strong></p>';
		$content .= '<p>';
		$content .= esc_html__( 'Requests go to https://api.openai.com/v1/chat/completions over HTTPS. OpenAI, L.L.C. processes the data according to their terms and privacy policy. OpenAI may use API data to improve their services. API usage may incur costs based on OpenAI pricing. You are responsible for complying with OpenAI usage policies.', 'dinoai-audit' );
		$content .= '</p>';
		$content .= '<p>';
		$content .= '<a href="' . esc_url( $terms_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'OpenAI Terms of Use', 'dinoai-audit' ) . '</a>';
		$content .= ' · ';
		$content .= '<a href="' . esc_url( $privacy_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'OpenAI Privacy Policy', 'dinoai-audit' ) . '</a>';
		$content .= ' · ';
		$content .= '<a href="' . esc_url( $pricing_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'OpenAI Pricing', 'dinoai-audit' ) . '</a>';
		$content .= '</p>';

		wp_add_privacy_policy_content( 'DinoAI Audit', wp_kses_post( $content ) );
	}
}
