=== Micro Site Care: Last Updated ===
Contributors: anomalousdevelopers
Tags: last updated, modified date, freshness, content age
Requires at least: 5.9
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display a "last updated" label on posts when content has been modified after the original publish date.

== Description ==

Micro Site Care: Last Updated automatically injects a configurable label when a post's modified date is later than its publish date.

* Global label template with `{date}` token.
* Three position options: above title, below title, or end of content.
* Optional days threshold — only show the label after N days.
* Shortcode `[msclu_last_updated]` for manual placement.
* Filter hook `msclu_last_updated_output` for developer customisation.

Upgrade to **Last Updated Pro** for relative date formatting (e.g. "Updated 3 days ago"), style presets (muted/pill/badge), and per-post force show/hide override.

== Installation ==

1. Upload the `msc-last-updated` folder to `wp-content/plugins/`.
2. Activate through **Plugins > Installed Plugins**.
3. Navigate to **Site Care > Last Updated** to configure.

== Changelog ==

= 0.1.0 =
* Initial release.
