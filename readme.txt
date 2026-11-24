=== MSC: Post Last Updated Date ===
Contributors: djm56
Tags: last updated date, last modified date, show modified date, dateModified schema, content freshness
Requires at least: 5.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.6.4
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show a "Last Updated" date on posts and pages. SEO-friendly time markup, custom labels and date formats, works with any theme. No JavaScript.

== Description ==

**Display the last updated date on your WordPress posts, pages and custom post types — automatically, no theme editing required.**

Readers and search engines both favour fresh content. MSC: Post Last Updated Date shows exactly when a post was last modified, with a customisable label, your choice of date format, and clean HTML5 `<time>` markup with an ISO 8601 timestamp.

**Key features:**

* Show the last updated date automatically before content, after content, or in both positions.
* Set placement to Manual and use the template tag in your theme for full control over where the label appears.
* Customise the label text with a flexible template — use `%s` to insert the date, or omit it to show a static label.
* Choose between the WordPress site date format or a custom PHP date format string.
* Optionally show the label only when a post has actually been edited after publishing (on by default).
* Target specific post types, or exclude selected post types from an all-types baseline — works with any public custom post type.
* Clean HTML5 `<time>` element output with an ISO 8601 `datetime` attribute for SEO and screen readers.
* Optional relative dates ("3 days ago") and a `[msclu_last_updated]` shortcode to place the date anywhere.
* Lightweight: no JavaScript, no external services, a single option row — zero tracking, GDPR-friendly.
* Translated into 20 languages (German, Spanish, French, Italian, Japanese, Dutch, Portuguese, Russian, Simplified Chinese, Turkish, Polish, Indonesian, Swedish, Ukrainian, Arabic).
* Developer-friendly: four filter hooks to customise visibility, label, CSS classes, and final HTML output.

== Installation ==

1. Upload the `micro-site-care-post-last-updated-date` folder to the `/wp-content/plugins/` directory, or install directly from the WordPress plugin screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Go to **Settings → MSC Post Last Updated Date** to configure the plugin.

== Frequently Asked Questions ==

= How do I show the last updated date on my WordPress posts? =

Activate the plugin — that's it. By default a "Updated {date}" label appears below the content of any post or page that has been edited after publishing. Fine-tune the placement, label and date format under **Settings → MSC Post Last Updated Date**.

= Why is the label not appearing on my posts? =

1. Go to **Settings → MSC Post Last Updated Date** and confirm **Enable output** is ticked.
2. Make sure the relevant post type (e.g. Post or Page) is selected in the **Post types** list.
3. If **Only show when modified date differs from publish date** is ticked (the default), the label only appears on posts that have been edited after their original publish date.
4. If **Automatic placement** is set to **Manual only**, nothing is injected automatically — you need to add the template tag to your theme.

= Does this plugin change my published date or modify my posts? =

No. It is display-only: the label is added at render time via a content filter. Nothing is written to your posts, and your published and modified dates are untouched.

= How do I place the date exactly where I want in my theme? =

Set **Automatic placement** to **Manual only** on the Settings page, then add the following in your theme template where you want the label:

`<?php msclup_the_last_updated(); ?>`

See the **Usage & Support** tab on the settings page for full template tag documentation and examples.

= Is there a shortcode? =

Yes. Use `[msclu_last_updated]` anywhere shortcodes run (posts, pages, widgets). Optional attributes: `post_id` (defaults to the current post) and `relative="true"` / `relative="false"` to override the date style for that instance.

= Can I show a relative date like "3 days ago"? =

Yes. Set **Date format source** to **Relative** on the settings page. The visible label reads "Updated 3 days ago", while the machine-readable `<time datetime>` attribute stays an absolute ISO-8601 timestamp for SEO.

= Can I change the label text ("Last updated", "Last checked", …)? =

Yes. Use the **Label template** setting. The `%s` token is replaced with the formatted date. Examples:

* `Updated %s` outputs `Updated March 28, 2026`
* `Last checked: %s` outputs `Last checked: March 28, 2026`
* Omitting `%s` entirely shows the label text with no date appended.

= Can I use a custom date format? =

Yes. Set **Date format source** to **Custom** and enter a PHP date format string in the **Custom date format** field. The **Usage & Support** tab includes a quick reference table and a link to the full WordPress date format documentation.

= Can I show the date only when a post has actually been edited? =

Yes — that is the default behaviour. The **Only show when modified date differs from publish date** setting suppresses the label on posts that have never been edited after publishing, so "Updated" always means something.

= Does it add schema.org structured data for SEO? =

The plugin outputs a semantic HTML5 `<time>` element with a machine-readable ISO 8601 `datetime` attribute, which search engines can parse. It does not inject JSON-LD structured data — your SEO plugin's `dateModified` schema (which WordPress themes and SEO plugins generate from the same modified date) is complemented, not duplicated.

= How do I style the date label? =

The plugin outputs:

`<p class="msclu-last-updated"><time datetime="2026-03-28T14:30:00+00:00">Updated March 28, 2026</time></p>`

Style the `.msclu-last-updated` class in your theme stylesheet or the Customizer's Additional CSS.

= Does it work with custom post types? =

Yes. All registered public post types appear in the **Post types** list on the settings page, in both include and exclude modes.

= Will it slow down my site? What data does it store? =

No measurable impact: the plugin loads no JavaScript, no stylesheets and makes no external requests. It stores a single options row and filters content only on singular views. Uninstalling removes the option — no data is left behind.

== Screenshots ==

1. Last updated date settings — choose placement, customise the label, pick a date format, and target post types for the last-modified date display.
2. Usage & Support tab — template tag examples and label reference for showing the last updated date anywhere in your theme.
3. Front-end last updated date on a post — the "Updated" label with schema-friendly HTML5 time markup, displayed below the content.

== Changelog ==

= 1.6.4 =
* Changed: Dev tooling security patch — composer dev-dependencies updated to clear CVE-2026-65954, CVE-2026-67434, and CVE-2026-45293; `composer audit` is now clean.
* Note: No plugin source changes required — zero shipping-code findings in this scan cycle.

= 1.6.3 =
* Accessibility: added table header scope attributes, fieldset legends, and ARIA descriptions.

= 1.6.2 =
* Updated translations: added 8 new languages (Russian, Simplified Chinese, Turkish, Polish, Indonesian, Swedish, Ukrainian, Arabic) and refreshed all 20 bundled locales to 100% string coverage.

= 1.6.1 =
* Tested with WordPress 7.1. No functional changes.

= 1.6.0 =
* Changed: Renamed the "Usage & Support" tab to "Support".
* Changed: Support now links to the plugin's WordPress.org support forum; removed the Settings-tab support box and the old contact button.

= 1.5.0 =
* New: `[msclu_last_updated]` shortcode to display the last-updated date anywhere, with optional `post_id` and `relative` attributes.
* New: Optional relative-date mode ("3 days ago"); the `<time datetime>` attribute stays an absolute ISO-8601 timestamp for SEO.
* Improved: WordPress.org listing rewritten — clearer title, searchable tags, expanded FAQ covering styling, SEO markup, performance and data handling.
* Improved: Refined search tags and screenshot captions for discoverability.
* Fixed: Removed dead stylesheet enqueue referencing a file that was never shipped; the plugin now (accurately) loads zero assets on the front end.
* Developer documentation corrected: filter hook signatures, option defaults and the full 12-locale translation list.

= 1.4.2 =
* Tested with WordPress 7.0.2. No functional changes.

= 1.4.1 =
* Tested with WordPress 7.0. No functional changes.

= 1.4.0 =
* Adds complete translations for 10 additional locales (de_CH, de_DE, es_ES, es_MX, fr_CA, fr_FR, it_IT, ja, nl_BE, nl_NL). Regenerates .pot source template with correct file references. Populates readme.txt translations for all 12 locales.

= 1.3.0 =
* Adds Brazilian and European Portuguese translations. No functional changes.

= 1.2.0 =
* Fixed: Admin settings page redirect URL pointed to `admin.php` instead of `options-general.php`; success notice now appears correctly after saving.
* Fixed: Label template without `%s` was silently replaced with the default `Updated %s`; it now renders as-is.
* Added: Dedicated Usage &amp; Support documentation tab with template tag reference, label examples, date format quick-reference, FAQ, and support link.
* Added: Support sidebar panel on the Settings tab.
* Removed: Debug `error_log()` calls from all Free plugin files.
* Removed: Unnecessary `wp_cache_delete()` calls.

= 1.0.0 =
* Initial public release.
* Automatic content injection with before, after, both, and manual placement modes.
* Configurable label template with `%s` date placeholder.
* Site or custom date format source.
* Visibility condition: suppress label when modified date matches publish date.
* Include or exclude post type targeting mode.
* PHP template tags: `msclup_the_last_updated()` and `msclup_get_last_updated()`.
* HTML5 `<time>` element output with ISO 8601 `datetime` attribute.
* Developer filter hooks: `msclu_should_display`, `msclu_label_text`, `msclu_wrapper_classes`, `msclu_output_html`.
* Two-tab admin settings page with Usage & Support documentation tab.

== Upgrade Notice ==

= 1.6.4 =
Security scan maintenance release: updates composer dev-dependencies to clear recent CVEs. No functional changes — safe update.

= 1.6.3 =
Accessibility: table header scopes, fieldset legends, and ARIA descriptions for assistive technology users. Safe update.

= 1.6.2 =
Updated translations: 8 new languages added (Russian, Simplified Chinese, Turkish, Polish, Indonesian, Swedish, Ukrainian, Arabic) and all 20 locales refreshed to 100% coverage.

= 1.6.1 =
Compatibility update: confirmed tested against WordPress 7.1. No functional changes — safe update.

= 1.5.0 =
Improved WordPress.org listing and FAQ; removes a dead stylesheet enqueue. No behaviour changes — safe update.

= 1.4.2 =
* Tested with WordPress 7.0.2. No functional changes.

= 1.4.1 =
* Tested with WordPress 7.0. No functional changes.

= 1.4.0 =
* Adds complete translations for 10 additional locales. No functional changes.

= 1.3.0 =
Adds Brazilian and European Portuguese translations. No functional changes.

= 1.2.0 =
Fixes settings-save redirect and label template behaviour. Adds Usage & Support documentation tab and redesigned settings layout.

= 1.0.0 =
Initial plugin release.
