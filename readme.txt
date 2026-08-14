=== DinoAI Audit ===
Contributors: wpdino, alexus450
Tags: ai, content, seo, accessibility, alt text
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI-powered WordPress website audits and optimization. Improve content, generate SEO text, write image alt text, and run a site audit.

== Description ==

DinoAI Audit brings practical AI tools into the WordPress dashboard. It is built by WPDINO to help you audit and optimize WordPress sites.

DinoAI Audit is not a generic chatbot. It focuses on WordPress workflows:

* Improve, rewrite, shorten, expand, or summarize selected content
* Generate an SEO title, meta description, and keyword suggestions
* Generate accessibility-focused image alt text
* Run a basic site audit with a simple score and recommendations

= How it works =

1. Connect your own OpenAI API key in DinoAI Audit → Settings.
2. Test the connection.
3. Open a post, select text, and choose a DinoAI Audit action.
4. Review the result, then copy, insert, or replace the selection yourself.

DinoAI Audit never replaces content automatically.

= External services =

When you run a DinoAI Audit action, the selected text, post excerpt used for SEO, or image used for alt text is sent to the configured AI provider. In this version that provider is [OpenAI](https://openai.com/).

* Data is sent only when a logged-in user with the required capability starts an action
* Your OpenAI API key is stored on your site and used for those requests
* DinoAI Audit does not store full prompts or full AI responses
* OpenAI terms and privacy policy apply to data they receive: [OpenAI Privacy Policy](https://openai.com/policies/privacy-policy)

= What this plugin does not do =

The basic audit is not a complete security, accessibility, SEO, or performance scan. DinoAI Audit complements dedicated SEO plugins rather than replacing them.

== Installation ==

1. Upload the `dinoai-audit` folder to `/wp-content/plugins/`.
2. Activate DinoAI Audit through the Plugins screen.
3. Go to DinoAI Audit → Settings.
4. Enter your OpenAI API key, choose a model, and test the connection.

== Frequently Asked Questions ==

= Do I need an OpenAI account? =

Yes. DinoAI Audit uses your own API key. Create one in your OpenAI account, then save it in DinoAI Audit settings.

= Does DinoAI Audit replace Yoast or Rank Math? =

No. It can generate a title, meta description, and keyword ideas. You decide what to copy into your SEO plugin.

= Why can't I generate alt text? =

Image analysis needs a vision-capable model such as gpt-4o-mini or gpt-4o. If the selected model cannot analyze images, DinoAI Audit shows a clear message instead of failing silently.

= Does the plugin send my content automatically? =

No. Content is sent only when you click an action.

= Where is my API key stored? =

On your WordPress site in a plugin option. It is not printed in frontend HTML or unauthenticated REST responses.

== Screenshots ==

1. DinoAI Audit settings with AI provider connection.
2. Content actions in the block editor sidebar.
3. Basic site audit score and recommendations.

== Changelog ==

= 1.0.0 =
* Initial release: content actions, SEO assistance, image alt text, and a basic site audit.

== Upgrade Notice ==

= 1.0.0 =
First public release of DinoAI Audit.
