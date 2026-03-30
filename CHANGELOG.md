# Changelog

All notable changes to MSC Last Updated are documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

## [1.3.0] - 2026-03-28

### Added
- Brazilian Portuguese (`pt_BR`) translation: full translation of all UI strings.
- European Portuguese (`pt_PT`) translation: full translation of all UI strings, using Portugal-Portuguese vocabulary (e.g. "definições", "separador", "ficheiro").
- Regenerated `.pot` source template file for version 1.3.0.
- Added `translators:` comments to all strings containing placeholders, satisfying WP.org and WP-CLI i18n validation.
- Numbered printf placeholders (`%1$s`, `%2$s`) in multi-argument translatable strings.

---

## [1.2.0] - 2026-03-28

### Fixed
- Admin settings page redirect URL was pointing to `admin.php` instead of `options-general.php`, causing the success notice never to appear after saving.
- Label template without `%s` was silently replaced with the default `Updated %s`; it now renders as-is with no date appended, as intended.

### Changed
- Settings page layout redesigned: flexbox two-column layout removes the blank-space rendering issue caused by WordPress core IDs (`#col-left`, `#col-right`). Sidebar panels (Upgrade to Pro, Support) now reliably float beside the form.
- `$_GET['updated']` success notice now requires the value to equal `'1'`, not just be present.

### Added
- Dedicated **Usage & Support** documentation tab with template tag reference, label template examples, date format quick-reference table, FAQ, and support link.
- **Upgrade to Pro** and **Support** sidebar panels on the Settings tab when Pro is not active.

### Removed
- Debug `error_log()` calls removed from all Free plugin files (required for WP.org acceptance and production safety).
- Unnecessary `wp_cache_delete()` calls removed (`update_option()` handles cache invalidation internally).

---

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
