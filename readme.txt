=== DinoAI Audit ===
Contributors: wpdino, alexus450
Donate link: https://paypal.me/dinostd/10usd
Tags: ai, content, seo, accessibility, alt text
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
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

This plugin connects to OpenAI only after you save your own API key. See **External Services** and **Privacy Policy** below.

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

= What data is sent to OpenAI? =

Only the content needed for the action you start: selected text, post title and content for SEO drafts, or the selected image for alt text, plus your API key. A short test prompt is sent when you click Test connection. The basic site audit does not send post content to OpenAI.

= Does this cost money? =

The plugin is free. OpenAI API usage is billed to your OpenAI account. See https://openai.com/pricing

== Screenshots ==

1. DinoAI Audit settings with AI provider connection.
2. Content actions in the block editor sidebar.
3. Basic site audit score and recommendations.

== Changelog ==

= 1.1.0 =
* Writing tone presets (Professional, Casual, Friendly, Concise) for content and SEO drafts.
* Site-wide custom instructions for AI actions.
* Copy SEO title and meta description into Yoast SEO or Rank Math with one click.
* Setup wizard checklist: connect key, test connection, try Improve.

= 1.0.2 =
* Minor fix.

= 1.0.1 =
* Clearer messages when an OpenAI API key or billing check fails.
* Filter audit findings by severity and category.
* Settings remembers your last tab.
* Dismissible “What’s new” notice after updates.
* Fixed documentation and WordPress.org support links.
* Fixed masked API key length unit test.

= 1.0.0 =
* Initial release: content actions, SEO assistance, image alt text, and a basic site audit.

== Upgrade Notice ==

= 1.1.0 =
Tone presets, custom instructions, Yoast/Rank Math handoff, and a setup wizard.

= 1.0.1 =
Polish release: clearer AI errors, audit filters, remembered settings tab, and link fixes.

= 1.0.0 =
First public release of DinoAI Audit.

== External Services ==

This plugin uses the following third-party/external service:

= OpenAI API Service =

**What the service is and what it is used for:**
OpenAI provides artificial intelligence services through their API. DinoAI Audit uses the OpenAI Chat Completions API to improve or rewrite selected text, draft SEO titles, meta descriptions, and keyword ideas, generate image alt text, and test that your API key works.

**What data is sent and when:**
- **Selected or pasted text** - Only when you run a content action (Improve, Rewrite, Shorten, Expand, or Summarize)
- **Post title and post content** - Only when you generate an SEO title, meta description, or keyword ideas (plain text, truncated)
- **Image data** - Only when you generate alt text (image sent as a data URL or image URL, plus optional caption)
- **Test prompt** - Only when you click Test connection (a short "OK" check)
- **API key** - Your OpenAI API key (stored in the WordPress database, transmitted only to OpenAI when making API requests)

**When data is sent:**
Data is only sent when:
1. You have saved an OpenAI API key in DinoAI Audit → Settings
2. A logged-in user with the required capability starts an action or clicks Test connection
3. The request is made from the WordPress admin or editor (not from public site visitors)

The basic site audit runs on your WordPress site and does **not** send post content to OpenAI.

**Service provider information:**
- Service provided by: OpenAI, L.L.C.
- API Endpoint: `https://api.openai.com/v1/chat/completions`
- Terms of Use: https://openai.com/policies/terms-of-use
- Privacy Policy: https://openai.com/policies/privacy-policy
- API Documentation: https://platform.openai.com/docs/
- Pricing: https://openai.com/pricing

**Important notes:**
- This service is only used when you configure an OpenAI API key in the plugin settings
- No data is sent to external services unless you explicitly configure the OpenAI integration
- Content generation must be manually triggered by a logged-in user
- DinoAI Audit never replaces post content automatically
- All data transmission is encrypted using HTTPS
- All data processing is handled by OpenAI according to their terms and privacy policy
- OpenAI may use API data to improve their services (see OpenAI's privacy policy for details)
- You are responsible for ensuring compliance with OpenAI's usage policies
- API usage may incur costs based on OpenAI's pricing (see https://openai.com/pricing)

== Privacy Policy ==

This plugin does not collect, store, or share any personal data by default. All data remains on your website and is not transmitted to external servers unless you configure the OpenAI integration.

**Data Transmission:**
- **OpenAI Integration**: When configured, selected text, post title and content used for SEO drafts, or an image used for alt text is sent to OpenAI's API. A short test prompt is sent when you click Test connection. OpenAI may use this data to improve their services as outlined in their privacy policy.
- **Site Audit**: The basic audit uses WordPress data on this site only. It does not send post content to OpenAI.

**Data Storage:**
- API keys are stored in your WordPress database
- Usage counts (request totals, module, model, approximate token usage, and timestamps) are stored on this site
- Full prompts and full AI responses are not stored
- No visitor or analytics data is collected by the plugin

**User Control:**
- The OpenAI integration is optional and must be explicitly configured
- Actions must be started by a logged-in user with the required capability
- You can disable individual modules in DinoAI Audit → Settings
- You can remove the API key at any time

Suggested privacy policy text is also added under Settings → Privacy in WordPress.
