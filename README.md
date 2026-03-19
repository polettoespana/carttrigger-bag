# CartTrigger – BAG

<p>
  <img src="https://img.shields.io/badge/version-2.0.9-0a0a23?style=flat-square" alt="Version 2.0.9">
  <img src="https://img.shields.io/badge/WordPress-6.3%2B-3858e9?style=flat-square&logo=wordpress&logoColor=white" alt="WordPress 6.3+">
  <img src="https://img.shields.io/badge/WooCommerce-required-96588a?style=flat-square&logo=woocommerce&logoColor=white" alt="WooCommerce required">
  <img src="https://img.shields.io/badge/PHP-7.4%2B-777bb4?style=flat-square&logo=php&logoColor=white" alt="PHP 7.4+">
  <img src="https://img.shields.io/badge/license-GPLv2-38a169?style=flat-square" alt="GPLv2">
</p>

**BAG** stands for **Brand · Awards · Gallery**. A WooCommerce plugin that extends the native brand taxonomy with custom fields, award badges, and lifestyle image galleries — all managed from a clean card-based admin UI, with shortcodes and PHP helpers for full frontend flexibility.

## Modules

### Brand · Custom Fields

Attach unlimited key–value pairs to any brand. Output them anywhere via shortcode or PHP helper. CSS classes are fully customisable, including Tailwind arbitrary-value classes (e.g. `text-[11px]`).

### Brand · Awards

Manage awards and recognitions per brand — optional logo, award name, event name, and year. Display them as a styled card list.

### Brand · Gallery

Upload a curated image gallery per brand. Display it as a responsive grid with optional native WooCommerce lightbox (PhotoSwipe). Images can be removed individually and reordered by drag & drop.

## Shortcodes

On a brand taxonomy page the `brand` attribute is optional — the plugin auto-detects the current term.

### `[ctbag_custom_fields]`

```
[ctbag_custom_fields
  brand="slug"
  wrapper_class="grid grid-cols-2 sm:grid-cols-3 gap-x-8 gap-y-4 max-w-2xl"
  dt_class="font-grotesk text-xs font-semibold uppercase tracking-widest text-blu-800/40 mb-1"
  dd_class="font-inter text-sm text-blu-900"
]
```

### `[ctbag_awards]`

```
[ctbag_awards
  brand="slug"
  title="Awards"
  title_class="editorial-label block mb-6"
  line_class="line-deco w-20 mb-10"
  wrapper_class="flex flex-col divide-y divide-blu-900/8 max-w-2xl"
  card_class="flex items-start gap-4 py-5"
  img_class="w-10 h-10 object-contain shrink-0 opacity-70 mt-0.5"
  name_class="font-grotesk text-sm text-blu-900 leading-snug"
  event_class="font-inter text-xs text-blu-800/60 mt-1"
  year_class="font-mono-accent text-xs text-blu-800/40 block mt-1"
]
```

### `[ctbag_gallery]`

```
[ctbag_gallery
  brand="slug"
  title="Gallery"
  title_class="editorial-label block mb-6"
  line_class="line-deco w-20 mb-10"
  wrapper_class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3"
  item_class="block aspect-square overflow-hidden group"
  img_class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
  size_thumb="medium"
  size_full="large"
  lightbox="1"
]
```

> **Tip:** use `size_thumb="medium"` (300 px) for multi-column grids instead of the default `medium_large` (768 px) to reduce image weight by 4–6× with no visible quality loss.

## PHP helpers

All shortcodes are also available as direct PHP functions — recommended when using Tailwind arbitrary-value classes (e.g. `text-[11px]`) that would otherwise be mangled by the WordPress shortcode parser:

```php
echo ctbag_custom_fields([
    'wrapper_class' => 'grid grid-cols-2 gap-4',
    'dt_class'      => 'text-[10px] uppercase tracking-widest',
]);

echo ctbag_awards([
    'title'         => __('Awards', 'your-textdomain'),
    'wrapper_class' => 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4',
    'card_class'    => 'flex flex-col items-center text-center p-5 border aspect-square',
]);

echo ctbag_gallery([
    'title'      => __('Gallery', 'your-textdomain'),
    'size_thumb' => 'medium',
    'lightbox'   => '1',
]);
```

## Admin UI

| Module               | Features                                                                               |
| -------------------- | -------------------------------------------------------------------------------------- |
| **HTML Description** | TinyMCE editor on both Add and Edit brand screens                                      |
| **Custom Fields**    | Key–value repeater with drag & drop reordering via handle icon                         |
| **Awards**           | Logo upload, event name, year — drag & drop reordering                                 |
| **Gallery**          | Multi-image uploader, × button for individual removal on hover, drag & drop reordering |

All modules are grouped in collapsible card panels with a built-in shortcode reference.

## Requirements

- WordPress **6.3** or later
- WooCommerce _(required, 8.0+ recommended)_
- PHP **7.4** or later

Tested with WordPress **6.9** and WooCommerce **10.6.0**.

## Installation

1. Clone this repository or download the ZIP and upload to `/wp-content/plugins/`.
2. Activate the plugin from **Plugins** in your WordPress admin.
3. Navigate to **Products → Brands** and edit any brand to start adding content.

## Changelog

### 2.0.9

- Fix: corrected Contributors username in readme.txt to match the WordPress.org plugin owner account (`polettoespana`).

### 2.0.8

- Perf: PhotoSwipe CSS and JS are now enqueued only on brand pages that have a gallery. Brands without a gallery no longer load 4 unnecessary assets (2 CSS + 2 JS).

### 2.0.7

- Fix: gallery images not saved after selection in the media uploader. Root cause: HTML input had `id="ctb_gallery"` while the admin JS targeted `#ctbag_gallery`, so the hidden field was never updated before form submission.

### 2.0.6

- Fix: fatal error on brand save due to mismatched method name after 2.0.5 prefix rename (`ctbag_save_term_meta`).
- Fix: added one-time migration to move existing term meta from legacy `ctb_*` keys to `ctbag_*` keys.

### 2.0.5

- Compliance: renamed all shortcodes and PHP helper functions from `ctb_*` to `ctbag_*` to meet the WordPress.org 5-character prefix requirement.
- Compliance: extracted inline gallery lightbox JavaScript to an external file loaded via `wp_enqueue_script()`.

### 2.0.4

- Gallery: individual image removal with an × button on hover, without reopening the media uploader.
- Gallery: drag & drop reordering of thumbnails (jQuery UI Sortable).
- Custom Fields: drag & drop reordering via a handle icon.
- Awards: drag & drop reordering via a handle icon.

### 2.0.3

- Fix: double-quote characters in award names, event names, and custom field values were corrupted on save. Awards and custom fields are now stored as native PHP arrays via `maybe_serialize()`.
- Fix: accented characters (é, à, ó…) in award/field text were silently dropped on some server configurations. Replaced `sanitize_text_field()` with `wp_strip_all_tags()`.

### 2.0.0

- Complete rewrite with professional admin UI (module cards).
- HTML Description module with TinyMCE editor on Add and Edit brand screens.
- Native WooCommerce lightbox (PhotoSwipe) support for gallery shortcode.
- Plugin fully internationalised — translations for Italian (it_IT) and Spanish (es_ES).

[GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html) — developed by [Poletto 1976 S.L.U.](https://poletto.es)
