<?php
/**
 * Centralized prompt templates.
 *
 * @package WPDINO\DinoAI
 */

namespace WPDINO\DinoAI;

defined( 'ABSPATH' ) || exit;

/**
 * Builds prompts by key and applies extension filters.
 *
 * @since 1.0.0
 */
class Prompt_Manager {

	/**
	 * Returns a prompt for a given key.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $prompt_key Prompt identifier.
	 * @param array<string, mixed> $context    Replacement context.
	 * @return string|\WP_Error
	 */
	public function get_prompt( $prompt_key, array $context = array() ) {
		$templates = $this->get_templates();

		if ( ! isset( $templates[ $prompt_key ] ) ) {
			return new \WP_Error(
				'dinoai_unknown_prompt',
				__( 'DinoAI could not complete the request.', 'dinoai' )
			);
		}

		$prompt = $this->interpolate( $templates[ $prompt_key ], $context );

		/**
		 * Filters a prompt before it is sent to the AI provider.
		 *
		 * @since 1.0.0
		 *
		 * @param string               $prompt     Prompt text.
		 * @param string               $prompt_key Prompt identifier.
		 * @param array<string, mixed> $context    Context data.
		 */
		return apply_filters( 'dinoai_prompt', $prompt, $prompt_key, $context );
	}

	/**
	 * Whether the prompt expects structured JSON.
	 *
	 * @since 1.0.0
	 *
	 * @param string $prompt_key Prompt identifier.
	 * @return bool
	 */
	public function is_structured( $prompt_key ) {
		$structured = array(
			'seo.title',
			'seo.meta_description',
			'seo.keywords',
			'seo.analyze',
		);

		/**
		 * Filters which prompt keys request JSON responses.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $structured Prompt keys.
		 * @param string   $prompt_key Current key.
		 */
		$structured = apply_filters( 'dinoai_structured_prompts', $structured, $prompt_key );

		return in_array( $prompt_key, $structured, true );
	}

	/**
	 * Returns registered prompt keys.
	 *
	 * @since 1.0.0
	 *
	 * @return string[]
	 */
	public function keys() {
		return array_keys( $this->get_templates() );
	}

	/**
	 * Prompt templates.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, string>
	 */
	protected function get_templates() {
		$templates = array(
			'content.improve'      => 'You are an editor helping a WordPress author. Improve the following content for clarity, flow, and readability. Preserve the original meaning, language, and any HTML tags. Do not add a title. Return only the improved content, with no preamble or quotes.

Content:
{{input}}',
			'content.rewrite'      => 'You are an editor helping a WordPress author. Rewrite the following content with different wording while preserving the original meaning, language, and any HTML tags. Return only the rewritten content, with no preamble or quotes.

Content:
{{input}}',
			'content.shorten'      => 'You are an editor helping a WordPress author. Shorten the following content. Keep the key points, original language, and any HTML tags. Return only the shortened content, with no preamble or quotes.

Content:
{{input}}',
			'content.expand'       => 'You are an editor helping a WordPress author. Expand the following content with useful detail. Stay on topic, keep the original language, and preserve any HTML tags. Return only the expanded content, with no preamble or quotes.

Content:
{{input}}',
			'content.summarize'    => 'You are an editor helping a WordPress author. Summarize the following content in a concise paragraph. Keep the original language. Return only the summary, with no preamble or quotes.

Content:
{{input}}',
			'seo.title'            => 'You write SEO titles for WordPress posts. Create one SEO-friendly title based on the content. The title should be clear, specific, and about 50-60 characters. Do not use clickbait or keyword stuffing. Return JSON only: {"title":"..."}

Post title: {{title}}
Content:
{{input}}',
			'seo.meta_description' => 'You write meta descriptions for WordPress posts. Create one concise meta description based on the content. Aim for 140-155 characters. Do not use keyword stuffing. Return JSON only: {"meta_description":"..."}

Post title: {{title}}
Content:
{{input}}',
			'seo.keywords'         => 'You suggest search phrases for a WordPress post. Suggest 3 to 6 relevant, natural search phrases a reader might use. Do not stuff keywords. Return JSON only: {"keywords":["..."]}

Post title: {{title}}
Content:
{{input}}',
			'seo.analyze'          => 'You give practical SEO recommendations for a WordPress post. Provide 3 to 6 short, specific suggestions. Use severity values of "notice", "warning", or "error". Do not claim this is a complete SEO audit. Return JSON only: {"suggestions":[{"severity":"warning","message":"..."}]}

Post title: {{title}}
Content:
{{input}}',
			'image.alt_text'       => 'Write concise, accessibility-focused alt text for this image. Describe the meaningful visual content. Do not start with "image of" or "picture of". Do not stuff keywords. Keep it under 125 characters. Return only the alt text.

{{input}}',
			'audit.seo'            => 'Explain these basic WordPress SEO findings in plain language. Provide a short recommendation for each. Do not claim this is a complete SEO audit.

Findings:
{{input}}',
			'audit.accessibility'  => 'Explain these basic WordPress accessibility findings in plain language. Provide a short recommendation for each. Do not claim this is a complete accessibility audit.

Findings:
{{input}}',
			'audit.content'        => 'Explain these basic WordPress content findings in plain language. Provide a short recommendation for each.

Findings:
{{input}}',
			'connection.test'      => 'Reply with the single word OK.',
		);

		/**
		 * Filters the prompt template map.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, string> $templates Prompt key => template.
		 */
		return apply_filters( 'dinoai_prompt_templates', $templates );
	}

	/**
	 * Replaces {{placeholders}} with context values.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $template Template string.
	 * @param array<string, mixed> $context  Values.
	 * @return string
	 */
	protected function interpolate( $template, array $context ) {
		foreach ( $context as $key => $value ) {
			if ( ! is_scalar( $value ) ) {
				continue;
			}

			$template = str_replace( '{{' . $key . '}}', (string) $value, $template );
		}

		return $template;
	}
}
