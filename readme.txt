=== Micro Site Care: Last Updated ===
Contributors: anomalous
Tags: last updated, last modified, post date, content date, editorial
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display and control the last-updated date for your WordPress posts and pages, with flexible placement and label options.

== Description ==

MSC Last Updated adds a last-updated date label to your posts and pages. It is lightweight, uses no JavaScript, and integrates cleanly with any theme.

**Key features:**

* Show the last-updated date automatically before content, after content, or in both positions.
* Set placement to Manual and use the template tag in your theme for full control over where the label appears.
* Customise the label text with a flexible template — use `%s` to insert the date, or omit it to show a static label.
* Choose between the WordPress site date format or a custom PHP date format string.
* Optionally suppress the label when the modified date matches the publish date (suppress unchanged posts).
* Target specific post types, or exclude selected post types from an all-types baseline.
* Clean HTML5 `<time>` element output with an ISO 8601 `datetime` attribute for SEO and screen readers.
* Developer-friendly: four filter hooks to customise visibility, label, CSS classes, and final HTML output.
* Compatible with MSC Last Updated Pro for relative dates, style presets, per-post overrides, and shortcodes.

== Installation ==

1. Upload the `msc-last-updated` folder to the `/wp-content/plugins/` directory, or install directly from the WordPress plugin screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Go to **Settings → MSC Last Updated** to configure the plugin.

== Frequently Asked Questions ==

= The label is not appearing on my posts. What should I check? =

1. Go to **Settings → MSC Last Updated** and confirm **Enable output** is ticked.
2. Make sure the relevant post type (e.g. Post or Page) is selected in the **Post types** list.
3. If **Only show when modified date differs from publish date** is ticked, the label will only appear on posts that have been edited after their original publish date.
4. If **Automatic placement** is set to **Manual only**, nothing is injected automatically — you need to add the template tag to your theme.

= How do I place the label at a specific spot in my theme? =

Set **Automatic placement** to **Manual only** on the Settings page, then add the following in your theme template where you want the label:

`<?php msclup_the_last_updated(); ?>`

See the **Usage & Support** tab on the settings page for full template tag documentation and examples.

= Can I change the label text? =

Yes. Use the **Label template** setting. The `%s` token is replaced with the formatted date. Examples:

* `Updated %s` outputs `Updated March 28, 2026`
* `Last checked: %s` outputs `Last checked: March 28, 2026`
* Omitting `%s` entirely shows the label text with no date appended.

= What HTML does the plugin output? =

`<p class="msclu-last-updated"><time datetime="2026-03-28T14:30:00+00:00">Updated March 28, 2026</time></p>`

Style the `.msclu-last-updated` class in your theme stylesheet.

= Can I use a custom date format? =

Yes. Set **Date format source** to **Custom** and enter a PHP date format string in the **Custom date format** field. The **Usage & Support** tab includes a quick reference table and a link to the full WordPress date format documentation.

= Does it work with custom post types? =

Yes. All registered public post types appear in the **Post types** list on the settings page.

= Is there a Pro version? =

Yes. MSC Last Updated Pro adds relative dates ("3 days ago"), a hybrid mode (relative then absolute after a configurable threshold), style presets (muted, pill, badge), per-post show/hide overrides via a metabox, and a shortcode with per-instance attribute overrides.

== Screenshots ==

1. Settings tab — two-column layout with placement, label, date format, and post type options.
2. Usage & Support tab — template tag examples, label template reference, FAQ, and support link.
3. Example frontend output on a post — last-updated label displayed below the post content.

== Changelog ==

= 1.3.0 =
* Adds Brazilian and European Portuguese translations. No functional changes.

= 1.2.0 =
* Fixed: Admin settings page redirect URL pointed to `admin.php` instead of `options-general.php`; success notice now appears correctly after saving.
* Fixed: Label template without `%s` was silently replaced with the default `Updated %s`; it now renders as-is.
* Added: Dedicated Usage &amp; Support documentation tab with template tag reference, label examples, date format quick-reference, FAQ, and support link.
* Added: Upgrade to Pro and Support sidebar panels on the Settings tab.
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

= 1.3.0 =
Adds Brazilian and European Portuguese translations. No functional changes.

= 1.2.0 =
Fixes settings-save redirect and label template behaviour. Adds Usage & Support documentation tab and redesigned settings layout.

= 1.0.0 =
Initial plugin release.
