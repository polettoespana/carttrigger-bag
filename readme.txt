=== CartTrigger – BAG ===
Contributors: poletto1976
Tags: woocommerce, brands, awards, gallery, brand management
Requires at least: 6.3
Tested up to: 6.9.4
Requires PHP: 7.4
Requires Plugins: woocommerce
Stable tag: 2.0.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enhance WooCommerce brands with custom fields, awards badges, and lifestyle galleries with native lightbox support.

== Description ==

**CartTrigger – BAG** (Brand · Awards · Gallery) extends the WooCommerce native brand taxonomy with three powerful modules:

= Custom Fields =
Attach unlimited key–value pairs to any brand. Output them anywhere via shortcode or PHP helper function. CSS classes are fully customisable, including Tailwind arbitrary-value classes (e.g. `text-[11px]`).

= Awards =
Manage a list of awards and recognitions for each brand — with optional logo, award name, event name, and year. Display them as a styled list using the `[ctbag_awards]` shortcode.

= Gallery =
Upload a curated image gallery per brand. Display it as a responsive grid with the `[ctbag_gallery]` shortcode. Optional native WooCommerce lightbox (PhotoSwipe) via `lightbox="1"`.

= Shortcodes =

**`[ctbag_custom_fields]`**
Outputs brand custom fields as a `<dl>` list.

    [ctb_custom_fields brand="slug" wrapper_class="..." dt_class="..." dd_class="..."]

**`[ctbag_awards]`**
Outputs brand awards as a styled card list.

    [ctb_awards brand="slug" title="Awards" wrapper_class="..." card_class="..."]

**`[ctbag_gallery]`**
Outputs brand gallery as a responsive image grid.

    [ctb_gallery brand="slug" title="Gallery" wrapper_class="..." lightbox="1"]

= PHP Helper Functions =
All shortcodes are also available as direct PHP functions that bypass WordPress' shortcode parser — useful when Tailwind arbitrary-value classes (e.g. `text-[11px]`) would otherwise be mangled:

    echo ctbag_custom_fields(['wrapper_class' => 'grid grid-cols-2 gap-4']);
    echo ctbag_awards(['title' => __('Awards', 'your-textdomain')]);
    echo ctbag_gallery(['title' => __('Gallery', 'your-textdomain'), 'lightbox' => '1']);

= Professional Admin UI =
All brand meta fields are presented in a clean, card-based admin interface grouped by module (HTML Description, Custom Fields, Awards, Gallery), with collapsible shortcode reference built in. Custom fields and awards support drag & drop reordering. Gallery images can be removed individually (× button on hover) and reordered by dragging.

== Installation ==

1. Upload the `carttrigger-bag` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Products → Brands** and edit any brand to start adding content.

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =
Yes. WooCommerce must be active before activating CartTrigger – BAG. The plugin declares a `Requires Plugins: woocommerce` dependency.

= Does it require a specific WooCommerce version? =
WooCommerce 8.0 or higher is recommended. The plugin has been tested up to WooCommerce 10.5.

= Can I use Tailwind CSS classes in the shortcode attributes? =
Yes, but classes containing square brackets (e.g. `text-[11px]`) will be interpreted as shortcode closing tags by WordPress. Use the PHP helper functions instead — they bypass the shortcode parser entirely.

= Where is the gallery lightbox loaded? =
The lightbox uses WooCommerce's bundled PhotoSwipe library. It is only active when `lightbox="1"` is set. On pages where WooCommerce scripts are disabled, a graceful fallback (open image in new tab) is used automatically.

== Screenshots ==

1. Brand edit screen — plugin banner and module cards.
2. Custom Fields module with key–value repeater.
3. Awards module with logo upload, event, and year fields.
4. Gallery module with multi-image uploader.
5. Frontend gallery with WooCommerce lightbox active.

== Changelog ==

= 2.0.6 =
* Fix: fatal error on brand save due to mismatched method name after 2.0.5 prefix rename (`ctbag_save_term_meta`).
* Fix: added one-time migration to move existing term meta from legacy `ctb_*` keys to `ctbag_*` keys.

= 2.0.5 =
* Compliance: renamed all shortcodes and PHP helper functions from `ctb_*` to `ctbag_*` to meet the WordPress.org 5-character prefix requirement (`ctbag` = CartTrigger BAG).
* Compliance: extracted inline gallery lightbox JavaScript to an external file (`assets/js/ctbag-gallery-lightbox.js`) loaded via `wp_enqueue_script()`.
* Compatibility: WC tested up to 10.6.0.

= 2.0.4 =
* New: Gallery — individual image removal with an × button that appears on hover, without reopening the media uploader.
* New: Gallery — drag & drop reordering of thumbnails (jQuery UI Sortable).
* New: Custom Fields — drag & drop reordering of field rows via a handle icon.
* New: Awards — drag & drop reordering of award rows via a handle icon.

= 2.0.3 =
* Fix: double-quote characters (`"`) in award names, event names, and custom field values were corrupted on save. Root cause: WordPress calls `wp_unslash()` (stripslashes) on meta values inside `add_metadata()`, which stripped the backslash from `\"` in the JSON string, producing invalid JSON. Fix: awards and custom fields are now stored as native PHP arrays via `maybe_serialize()` — no backslash escaping involved, immune to the slashing issue. Backward-compatible: existing JSON-format data is still read correctly.
* Fix: accented characters (é, à, ó…) in award/field text were silently dropped on some server configurations. Replaced `sanitize_text_field()` with `wp_strip_all_tags()` to avoid the internal `wp_check_invalid_utf8()` call that incorrectly rejected valid multi-byte characters.

= 2.0.0 =
* Complete rewrite with professional admin UI (module cards).
* Added HTML Description module with TinyMCE editor on both Add and Edit brand screens.
* Added native WooCommerce lightbox (PhotoSwipe) support for gallery shortcode.
* Added activation notice with link to brand management screen.
* Plugin fully internationalised — translations available for Italian (it_IT) and Spanish (es_ES).

== Upgrade Notice ==

= 2.0.5 =
Breaking change: shortcodes and PHP helper functions renamed from `ctb_*` to `ctbag_*`. Update any shortcodes in your content accordingly.

= 2.0.4 =
New admin UX: drag & drop reordering for gallery, custom fields, and awards; single-image removal in gallery.

= 2.0.3 =
Bug fix: awards and custom fields with accented characters or typographic quotes now save correctly. No data migration needed.

= 2.0.0 =
Major rewrite. No data migration needed — all existing brand meta is preserved.
