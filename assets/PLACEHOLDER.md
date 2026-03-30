# WP.org Plugin Assets

This directory holds the images displayed on the plugin's WordPress.org listing page.
These files are **NOT included in the plugin zip** — they are uploaded directly via the WP.org SVN `assets/` folder at the repository root level (not inside the plugin folder).

---

## Required Before WP.org Submission

| File | Dimensions | Format | Required? | Used for |
|---|---|---|---|---|
| `banner-1544x500.png` | 1544 × 500 px | PNG or JPG | **Yes** | Plugin header banner (HiDPI / Retina) |
| `banner-772x250.png` | 772 × 250 px | PNG or JPG | Optional | Plugin header banner (standard DPI fallback) |
| `icon-256x256.png` | 256 × 256 px | PNG | **Yes** | Plugin icon in search results (HiDPI) |
| `icon-128x128.png` | 128 × 128 px | PNG | Optional | Plugin icon in search results (standard DPI) |
| `screenshot-1.png` | Any (16:9 recommended) | PNG or JPG | Strongly recommended | Must match Screenshot 1 caption in `readme.txt` |
| `screenshot-2.png` | Any (16:9 recommended) | PNG or JPG | Strongly recommended | Must match Screenshot 2 caption in `readme.txt` |
| `screenshot-3.png` | Any (16:9 recommended) | PNG or JPG | Strongly recommended | Must match Screenshot 3 caption in `readme.txt` |

---

## Screenshot Captions (from readme.txt)

```
1. Settings tab — two-column layout with placement, label, date format, and post type options.
2. Usage & Support tab — template tag examples, label template reference, FAQ, and support link.
3. Example frontend output on a post — last-updated label displayed below the post content.
```

Capture these from a WordPress installation with the plugin active and real content visible.

---

## Design Notes

- **Banner background:** Should reflect the plugin branding. Recommended: use the Anomalous brand colour palette.
- **Icon:** Should be a simple, recognisable icon — e.g. a calendar with a refresh/clock symbol. Must look clear at 128×128 px.
- **No text in the icon.** WP.org guidelines advise against relying on text inside icons.
- **Do not include the `.org` suffix** in any banner text.

---

## WP.org SVN Structure (for reference)

When submitting to WP.org, the SVN repository structure is:

```
/assets/
    banner-1544x500.png
    banner-772x250.png
    icon-256x256.png
    icon-128x128.png
    screenshot-1.png
    screenshot-2.png
    screenshot-3.png
/tags/
    1.3.0/
        msc-last-updated.php
        ...
/trunk/
    msc-last-updated.php
    ...
```

Assets live under `/assets/` at the root of the SVN repo — **not** inside any tag or trunk subfolder.

---

## IMPORTANT

Do **not** submit this placeholder file or any empty/dummy image files to WP.org.
Replace all images with real artwork before running `scripts/package.sh` or committing to the WP.org SVN.
