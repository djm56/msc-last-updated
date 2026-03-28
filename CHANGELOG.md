# Changelog

All notable changes to MSC Last Updated are documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2026-03-28

### Added
- Automatic content injection via `the_content` filter with four placement modes: after, before, both, and manual.
- Configurable label template with `%s` placeholder for the formatted date. Template without `%s` renders as-is with no date.
- Date format source: use the WordPress site date format or specify a custom PHP date format string.
- Visibility condition: optionally suppress the label when the modified date equals the publish date.
- Post type targeting: include selected types or exclude selected types from an all-types baseline.
- PHP template tags: `msclup_the_last_updated( $post_id )` (echo) and `msclup_get_last_updated( $post_id )` (return).
- HTML5 `<time>` element output with ISO 8601 `datetime` attribute for SEO and accessibility.
- Developer filter hooks: `msclu_should_display`, `msclu_label_text`, `msclu_wrapper_classes`, `msclu_output_html`.
- Settings extension hooks for Pro add-on: `msclu_settings_sections` (render), `msclu_settings_sanitized_options` (save).
- Native WordPress admin settings page at **Settings → MSC Last Updated**.
- Two-tab admin UI: Settings tab with two-column layout and sidebar; Usage & Support tab with template tag docs, label examples, date format reference, FAQ, and support link.
- Upgrade to Pro sidebar panel shown on the Settings tab when the Pro add-on is not active.
- Legacy menu slug redirects for backwards compatibility.
