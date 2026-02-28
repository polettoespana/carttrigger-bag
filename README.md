# CartTrigger – BAG

<p>
  <img src="https://img.shields.io/badge/version-2.0.4-0a0a23?style=flat-square" alt="Version 2.0.4">
  <img src="https://img.shields.io/badge/WordPress-6.3%2B-3858e9?style=flat-square&logo=wordpress&logoColor=white" alt="WordPress 6.3+">
  <img src="https://img.shields.io/badge/WooCommerce-required-96588a?style=flat-square&logo=woocommerce&logoColor=white" alt="WooCommerce required">
  <img src="https://img.shields.io/badge/PHP-7.4%2B-777bb4?style=flat-square&logo=php&logoColor=white" alt="PHP 7.4+">
  <img src="https://img.shields.io/badge/license-GPLv2-38a169?style=flat-square" alt="GPLv2">
</p>

<small>**BAG** stands for **Brand · Awards · Gallery**. A WooCommerce plugin that extends the native brand taxonomy with custom fields, award badges, and lifestyle image galleries — all managed from a clean card-based admin UI, with shortcodes and PHP helpers for full frontend flexibility.</small>

---

## Modules

### Brand · Custom Fields
<small>Attach unlimited key–value pairs to any brand. Output them anywhere via shortcode or PHP helper. CSS classes are fully customisable, including Tailwind arbitrary-value classes (e.g. `text-[11px]`).</small>

### Brand · Awards
<small>Manage awards and recognitions per brand — optional logo, award name, event name, and year. Display them as a styled card list.</small>

### Brand · Gallery
<small>Upload a curated image gallery per brand. Display it as a responsive grid with optional native WooCommerce lightbox (PhotoSwipe). Images can be removed individually and reordered by drag & drop.</small>

---

## Shortcodes

| Shortcode | Description |
|-----------|-------------|
| `[ctb_custom_fields]` | <small>Outputs brand custom fields as a `<dl>` list</small> |
| `[ctb_awards]` | <small>Outputs brand awards as a styled card list</small> |
| `[ctb_gallery]` | <small>Outputs brand gallery as a responsive image grid</small> |

### Parameters

**`[ctb_custom_fields brand="slug" wrapper_class="..." dt_class="..." dd_class="..."]`**

**`[ctb_awards brand="slug" title="Awards" wrapper_class="..." card_class="..."]`**

**`[ctb_gallery brand="slug" title="Gallery" wrapper_class="..." lightbox="1"]`**

---

## PHP helpers

<small>All shortcodes are also available as direct PHP functions — useful when Tailwind arbitrary-value classes (e.g. `text-[11px]`) would otherwise be mangled by the WordPress shortcode parser:</small>

```php
echo ctb_custom_fields(['wrapper_class' => 'grid grid-cols-2 gap-4']);
echo ctb_awards(['title' => __('Awards', 'your-textdomain')]);
echo ctb_gallery(['title' => __('Gallery', 'your-textdomain'), 'lightbox' => '1']);
```

---

## Admin UI

| Module | Features |
|--------|----------|
| **HTML Description** | <small>TinyMCE editor on both Add and Edit brand screens</small> |
| **Custom Fields** | <small>Key–value repeater with drag & drop reordering via handle icon</small> |
| **Awards** | <small>Logo upload, event name, year — drag & drop reordering</small> |
| **Gallery** | <small>Multi-image uploader, × button for individual removal on hover, drag & drop reordering</small> |

<small>All modules are grouped in collapsible card panels with a built-in shortcode reference.</small>

---

## Requirements

<small>

- WordPress **6.3** or later
- WooCommerce *(required, 8.0+ recommended)*
- PHP **7.4** or later

Tested with WordPress **6.7** and WooCommerce **10.5.2**.

</small>

---

## Installation

<small>

1. Clone this repository or download the ZIP and upload to `/wp-content/plugins/`.
2. Activate the plugin from **Plugins** in your WordPress admin.
3. Navigate to **Products → Brands** and edit any brand to start adding content.

</small>

---

## Changelog

### 2.0.4
<small>

- Gallery: individual image removal with an × button on hover, without reopening the media uploader.
- Gallery: drag & drop reordering of thumbnails (jQuery UI Sortable).
- Custom Fields: drag & drop reordering via a handle icon.
- Awards: drag & drop reordering via a handle icon.

</small>

### 2.0.3
<small>

- Fix: double-quote characters in award names, event names, and custom field values were corrupted on save. Awards and custom fields are now stored as native PHP arrays via `maybe_serialize()`.
- Fix: accented characters (é, à, ó…) in award/field text were silently dropped on some server configurations. Replaced `sanitize_text_field()` with `wp_strip_all_tags()`.

</small>

### 2.0.0
<small>

- Complete rewrite with professional admin UI (module cards).
- HTML Description module with TinyMCE editor on Add and Edit brand screens.
- Native WooCommerce lightbox (PhotoSwipe) support for gallery shortcode.
- Plugin fully internationalised — translations for Italian (it\_IT) and Spanish (es\_ES).

</small>

---

<small><a href="https://www.gnu.org/licenses/gpl-2.0.html">GPLv2 or later</a> — developed by <a href="https://poletto.es">Poletto 1976 S.L.U.</a></small>
