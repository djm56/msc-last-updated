# MSC Last Updated

Display and control the post last-updated date in flexible positions.

**Version:** 1.2.0 | **Requires WP:** 5.9+ | **Requires PHP:** 7.4+ | **License:** GPL-2.0+

---

## Features

- Show the last-updated date automatically **before content**, **after content**, **both**, or set to **Manual** for full theme control.
- Customise the label with a flexible template — use `%s` as the date placeholder, or omit it for a static label with no date.
- Choose between the **WordPress site date format** or a **custom PHP date format string**.
- Optionally suppress the label when the modified date matches the publish date (unchanged posts).
- **Include** specific post types or **exclude** selected types from an all-types baseline.
- Clean HTML5 `<time>` element output with an ISO 8601 `datetime` attribute for SEO and accessibility.
- Four developer filter hooks to customise visibility, label text, CSS classes, and final HTML output.
- Designed for extension by **MSC Last Updated Pro** (relative dates, style presets, per-post overrides, shortcode with attribute overrides).

---

## Requirements

| Requirement | Minimum |
|---|---|
| WordPress | 5.9 |
| PHP | 7.4 |
| MySQL | 5.6 |

---

## Installation

### Via WordPress admin

1. Go to **Plugins → Add New Plugin**.
2. Search for `MSC Last Updated`.
3. Click **Install Now**, then **Activate**.
4. Go to **Settings → MSC Last Updated** to configure.

### Manual upload

1. Download the latest release zip from [the releases page](https://anomalous.co.za).
2. Upload the `msc-last-updated` folder to `/wp-content/plugins/`.
3. Activate through **Plugins → Installed Plugins**.
4. Go to **Settings → MSC Last Updated** to configure.

---

## Configuration

Navigate to **Settings → MSC Last Updated**.

| Setting | Description | Default |
|---|---|---|
| **Enable output** | Master on/off for the last-updated label. | Enabled |
| **Automatic placement** | Where to inject the label: After content, Before content, Before and after, or Manual only. | After content |
| **Label template** | Text to display. Use `%s` where the formatted date should appear. Omit `%s` for a static label. | `Updated %s` |
| **Date format source** | Use the WordPress site date format, or specify a custom PHP date format string. | Site format |
| **Custom date format** | PHP date format string (e.g. `d/m/Y`). Only used when **Date format source** is set to Custom. | `F j, Y` |
| **Visibility condition** | When ticked, the label is suppressed on posts whose modified date equals their publish date. | Unticked |
| **Post type mode** | Include only selected types, or exclude selected types from all public post types. | Include selected |
| **Post types** | The post types to target based on the mode above. | `post` |

---

## Template Tags

Use these PHP functions in theme templates when **Automatic placement** is set to **Manual only**.

### `msclup_the_last_updated( $post_id = null )`

Echoes the rendered HTML directly. Use inside The Loop or pass a post ID.

```php
// Inside The Loop — uses current post
msclup_the_last_updated();

// Outside The Loop — pass a specific post ID
msclup_the_last_updated( 42 );
```

**Expected HTML output:**

```html
<p class="msclu-last-updated">
    <time datetime="2026-03-28T14:30:00+00:00">Updated March 28, 2026</time>
</p>
```

### `msclup_get_last_updated( $post_id = null )`

Returns the rendered HTML string instead of echoing. Useful for capturing output or passing to another function.

```php
$html = msclup_get_last_updated( get_the_ID() );
echo wp_kses_post( $html );
```

---

## Filter Hooks

| Hook | Signature | Description |
|---|---|---|
| `msclu_should_display` | `( bool $display, WP_Post $post )` | Return `false` to prevent the label from appearing on a specific post. |
| `msclu_label_text` | `( string $label, WP_Post $post )` | Override the rendered label string (including the date) before it is wrapped in the `<time>` element. |
| `msclu_wrapper_classes` | `( array $classes, WP_Post $post )` | Add or remove CSS classes on the `<p>` wrapper element. |
| `msclu_output_html` | `( string $html, WP_Post $post )` | Filter the complete final HTML string before it is injected or returned. |

**Example — hide the label on a specific post:**

```php
add_filter( 'msclu_should_display', function( $display, $post ) {
    return ( 99 === $post->ID ) ? false : $display;
}, 10, 2 );
```

**Example — add a custom wrapper class:**

```php
add_filter( 'msclu_wrapper_classes', function( $classes, $post ) {
    $classes[] = 'my-custom-class';
    return $classes;
}, 10, 2 );
```

---

## Internationalization

The plugin is fully translation-ready.

- **Text domain:** `msc-last-updated`
- **Domain path:** `/languages`
- **POT template:** `languages/msc-last-updated.pot`

Included translations:

| Locale | Language | File |
|---|---|---|
| `pt_BR` | Brazilian Portuguese | `msc-last-updated-pt_BR.po` / `.mo` |
| `pt_PT` | European Portuguese | `msc-last-updated-pt_PT.po` / `.mo` |

### Regenerate the POT file

Requires [WP-CLI](https://wp-cli.org/). Run from inside the plugin folder:

```bash
wp i18n make-pot . languages/msc-last-updated.pot \
  --domain=msc-last-updated \
  --exclude=node_modules,vendor,.git,tests
```

Or via Composer:

```bash
composer i18n:pot
```

### Compile a .po file to .mo

```bash
wp i18n make-mo languages/msc-last-updated-pt_BR.po languages/msc-last-updated-pt_BR.mo
```

Or compile all `.po` files in the languages directory at once:

```bash
wp i18n make-mo languages/
```

### Contributing a new translation

1. Copy `languages/msc-last-updated.pot` to `languages/msc-last-updated-{locale}.po`.
2. Edit the `.po` file with [Poedit](https://poedit.net/) or any `.po` editor.
3. Compile to `.mo` using the command above.
4. Submit both files via a pull request.

---

## Development

Install dependencies:

```bash
composer install
```

Lint PHP (PHPCS + WPCS):

```bash
composer lint
composer lint-fix
```

Lint JS/CSS (if applicable):

```bash
npm install
npm run lint
npm run lint-fix
```

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for the full version history.

---

## License

[GPL-2.0+](https://www.gnu.org/licenses/gpl-2.0.html)
