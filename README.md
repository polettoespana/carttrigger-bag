# CartTrigger – BAG

<p>
  <img src="https://img.shields.io/badge/version-2.0.4-0a0a23?style=flat-square" alt="Version 2.0.4">
  <img src="https://img.shields.io/badge/WordPress-6.3%2B-3858e9?style=flat-square&logo=wordpress&logoColor=white" alt="WordPress 6.3+">
  <img src="https://img.shields.io/badge/WooCommerce-required-96588a?style=flat-square&logo=woocommerce&logoColor=white" alt="WooCommerce required">
  <img src="https://img.shields.io/badge/PHP-7.4%2B-777bb4?style=flat-square&logo=php&logoColor=white" alt="PHP 7.4+">
  <img src="https://img.shields.io/badge/license-GPLv2-38a169?style=flat-square" alt="GPLv2">
</p>

**BAG** stands for **Brand · Awards · Gallery**. A WooCommerce plugin that extends the native brand taxonomy with custom fields, award badges, and lifestyle image galleries — all managed from a clean card-based admin UI, with shortcodes and PHP helpers for full frontend flexibility.

---

## Modules

### Brand · Custom Fields
Attach unlimited key–value pairs to any brand. Output them anywhere via shortcode or PHP helper. CSS classes are fully customisable, including Tailwind arbitrary-value classes (e.g. `text-[11px]`).

### Brand · Awards
Manage awards and recognitions per brand — optional logo, award name, event name, and year. Display them as a styled card list.

### Brand · Gallery
Upload a curated image gallery per brand. Display it as a responsive grid with optional native WooCommerce lightbox (PhotoSwipe). Images can be removed individually and reordered by drag & drop.

---

## Shortcodes

| Shortcode | Description |
|-----------|-------------|
| `[ctb_custom_fields]` | Outputs brand custom fields as a `<dl>` list |
| `[ctb_awards]` | Outputs brand awards as a styled card list |
| `[ctb_gallery]` | Outputs brand gallery as a responsive image grid |

### Parameters

**`[ctb_custom_fields brand="slug" wrapper_class="..." dt_class="..." dd_class="..."]`**

**`[ctb_awards brand="slug" title="Awards" wrapper_class="..." card_class="..."]`**

**`[ctb_gallery brand="slug" title="Gallery" wrapper_class="..." lightbox="1"]`**

---

## PHP helpers

All shortcodes are also available as direct PHP functions — useful when Tailwind arbitrary-value classes (e.g. `text-[11px]`) would otherwise be mangled by the WordPress shortcode parser:

```php
echo ctb_custom_fields(['wrapper_class' => 'grid grid-cols-2 gap-4']);
echo ctb_awards(['title' => __('Awards', 'your-textdomain')]);
echo ctb_gallery(['title' => __('Gallery', 'your-textdomain'), 'lightbox' => '1']);
```

---

## Admin UI

| Module | Features |
|--------|----------|
| **HTML Description** | TinyMCE editor on both Add and Edit brand screens |
| **Custom Fields** | Key–value repeater with drag & drop reordering via handle icon |
| **Awards** | Logo upload, event name, year — drag & drop reordering |
| **Gallery** | Multi-image uploader, × button for individual removal on hover, drag & drop reordering |

All modules are grouped in collapsible card panels with a built-in shortcode reference.

---

## Requirements

- WordPress **6.3** or later
- WooCommerce *(required, 8.0+ recommended)*
- PHP **7.4** or later

Tested with WordPress **6.7** and WooCommerce **10.5.2**.

---

## Installation

1. Clone this repository or download the ZIP and upload to `/wp-content/plugins/`.
2. Activate the plugin from **Plugins** in your WordPress admin.
3. Navigate to **Products → Brands** and edit any brand to start adding content.

---

## Changelog

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
- Plugin fully internationalised — translations for Italian (it\_IT) and Spanish (es\_ES).

---

[GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html) — developed by [Poletto 1976 S.L.U.](https://poletto.es)
