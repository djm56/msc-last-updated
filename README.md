# Micro Site Care: Last Updated

Automatically display a "last updated" label on posts and pages when content has been modified after publication.

## Features

- Automatically shows a "Last updated" timestamp when content is modified after publish
- Lightweight output filter with minimal overhead
- Shortcode support via `[msclu_last_updated]` (useful in templates)
- Styling hooks and filters for theme integration

## Requirements

- WordPress 5.9 or later
- PHP 7.4 or later

## Installation

### Upload via WordPress Admin

1. Compress the plugin folder into a ZIP file (for example `msc-last-updated.zip`).
2. Go to **Plugins > Add New** in your WordPress admin.
3. Click **Upload Plugin**, choose the ZIP file, and click **Install Now**.
4. After installation click **Activate Plugin**.

### Install via FTP / SFTP

1. Extract the plugin folder and upload the `msc-last-updated` directory to `wp-content/plugins/` on your server.
2. Visit **Plugins** in the WordPress admin and click **Activate** under *Micro Site Care: Last Updated*.

## Configuration

Configure the plugin at **Site Care > Last Updated** to set:

- Whether to display updated timestamps on posts, pages, or both
- The output format (date format string or relative times when paired with the Pro extension)
- Custom CSS classes for styling the timestamp

If the Pro extension is installed additional formatting options and per-post overrides are available at **Site Care > Last Updated Pro**.

## Usage

- To display the timestamp inside templates, use the shortcode: `[msclu_last_updated]`.
- Per-post overrides are available when the Pro plugin is installed.

## FAQ

- Q: Will this change the published date?
	- A: No — it only displays an additional "last updated" label; the published date remains unchanged.

## Compatibility with Pro

Installing `msc-last-updated-pro` enables relative date formats, style presets, and per-post override controls.

## Changelog

### 0.1.0
- Initial release.

## Support

For support and feature requests, open an issue or contact the maintainers.

## License

This plugin is licensed under the GNU General Public License v2 (or later). See the `LICENSE` file for details.

## Development & Linting

This repository contains development tooling (`composer.json`, `package.json`, `phpcs.xml.dist`, `.editorconfig`). These files are not included in packaged ZIPs and are intended for development and linting only. Run `composer install` then `npm run lint` or `npm run lint-fix` in the plugin directory to use the configured PHPCS setup.

---

Micro Site Care — small utilities to keep WordPress sites tidy.
