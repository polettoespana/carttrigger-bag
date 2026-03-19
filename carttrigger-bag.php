<?php

/**
 * Plugin Name:  CartTrigger – BAG
 * Plugin URI:   https://poletto.es/nuestros-servicios/eficiencia/ct-bag
 * Description:  Enhance WooCommerce with advanced brand management, awards badges, and lifestyle galleries built into native zoom.
 * Version:      2.0.9
 * Author:       Poletto 1976 S.L.U.
 * Author URI:   https://poletto.es
 * License:      GPLv2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  carttrigger-bag
 * Domain Path:  /languages
 * Requires Plugins: woocommerce
 * WC tested up to: 10.6.0
 */

if (! defined('ABSPATH')) {
    exit;
}

// Declare WooCommerce HPOS (Custom Order Tables) compatibility.
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

define('CTBAG_VERSION', '2.0.9');
define('CTBAG_DIR', plugin_dir_path(__FILE__));
define('CTBAG_URL', plugin_dir_url(__FILE__));

class CTBAG_CartTrigger_BAG
{
    public function __construct()
    {
        // ── 1. Brand Environment (HTML & Security) ────────────────────────────
        add_action('init', [$this, 'ctb_init_brands_environment'], 20);

        // ── 2. Admin Assets ───────────────────────────────────────────────────
        add_action('admin_enqueue_scripts', [$this, 'ctb_enqueue_admin_assets']);

        // ── 3. Admin Form Fields ──────────────────────────────────────────────
        add_action('product_brand_edit_form_fields', [$this, 'ctb_edit_form_fields'], 20, 2);
        add_action('product_brand_add_form_fields',  [$this, 'ctb_add_form_fields'],  10, 1);

        // ── 4. Save Meta ──────────────────────────────────────────────────────
        add_action('edited_product_brand',  [$this, 'ctbag_save_term_meta'], 10, 2);
        add_action('created_product_brand', [$this, 'ctbag_save_term_meta'], 10, 2);

        // ── 5. Frontend: Brand Info on Single Product ─────────────────────────
        add_action('woocommerce_single_product_summary', [$this, 'ctb_display_brand_info'], 5);

        // ── 6. Frontend: Shortcodes ───────────────────────────────────────────
        add_shortcode('ctbag_awards',        [$this, 'ctb_awards_shortcode']);
        add_shortcode('ctbag_custom_fields', [$this, 'ctb_custom_fields_shortcode']);
        add_shortcode('ctbag_gallery',       [$this, 'ctb_gallery_shortcode']);

        // ── 7. Frontend: Public Assets ───────────────────────────────────────
        add_action('wp_enqueue_scripts', [$this, 'ctb_enqueue_assets'], 20);

        // ── 8. Activation Notice ──────────────────────────────────────────────
        add_action('admin_notices', [$this, 'ctb_activation_notice']);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 1. BRAND ENVIRONMENT
    // ═══════════════════════════════════════════════════════════════════════════

    public function ctb_init_brands_environment()
    {
        // Allow rich HTML in term descriptions (remove WP's aggressive kses strip).
        if (has_filter('pre_term_description', 'wp_filter_kses')) {
            remove_filter('pre_term_description', 'wp_filter_kses');
        }
        if (has_filter('term_description', 'wp_kses_formatting')) {
            remove_filter('term_description', 'wp_kses_formatting');
        }
        // Re-apply a safer post-level filter (blocks <script> but allows images, links, etc.)
        add_filter('pre_term_description', 'wp_kses_post');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 2. ADMIN ASSETS
    // ═══════════════════════════════════════════════════════════════════════════

    public function ctb_enqueue_admin_assets($hook)
    {
        $screen = get_current_screen();
        if (! $screen || $screen->id !== 'edit-product_brand') {
            return;
        }

        wp_enqueue_media();

        wp_enqueue_style(
            'ctbag-admin',
            CTBAG_URL . 'assets/css/ctb-admin.css',
            [],
            CTBAG_VERSION
        );

        wp_enqueue_script(
            'ctbag-admin',
            CTBAG_URL . 'assets/js/ctb-admin.js',
            ['jquery', 'wp-util', 'jquery-ui-sortable'],
            CTBAG_VERSION,
            true
        );

        wp_localize_script('ctbag-admin', 'ctbagAdmin', [
            'mediaTitle'          => __('Select image', 'carttrigger-bag'),
            'mediaButton'         => __('Use image', 'carttrigger-bag'),
            'labelPlaceholder'    => __('Label', 'carttrigger-bag'),
            'valuePlaceholder'    => __('Value', 'carttrigger-bag'),
            'awardNamePlaceholder' => __('Award name', 'carttrigger-bag'),
            'awardEventPlaceholder' => __('Event (e.g. Vinum Extra 2024)', 'carttrigger-bag'),
            'awardYearPlaceholder' => __('Year', 'carttrigger-bag'),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 3. ADMIN FORM FIELDS
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Fields shown on the "Edit Brand" screen (term already exists, has term_id).
     */
    public function ctb_edit_form_fields($term, $taxonomy)
    {
        $custom_fields = $this->ctb_get_custom_fields($term->term_id);
        $awards        = $this->ctb_get_awards($term->term_id);
        $gallery_ids   = $this->ctb_get_gallery_ids($term->term_id);
?>

        <!-- ── Plugin Banner ────────────────────────────────────────────────── -->
        <tr class="ctb-banner-row">
            <td colspan="2">
                <?php wp_nonce_field('ctbag_save_term_meta', 'ctbag_nonce'); ?>
                <div class="ctb-plugin-banner">
                    <div class="ctb-plugin-header">
                        <strong class="ctb-plugin-name">CartTrigger – BAG</strong>
                        <span class="ctb-plugin-version">v<?php echo esc_html( CTBAG_VERSION ); ?></span>
                    </div>
                    <p class="ctb-plugin-tagline">Brand · Awards · Gallery for WooCommerce</p>
                </div>
            </td>
        </tr>

        <!-- ── TinyMCE Description ──────────────────────────────────────────── -->
        <tr class="ctb-module-row">
            <td colspan="2">
                <div class="ctb-module">
                    <h3 class="ctb-module-title">
                        <span class="dashicons dashicons-edit"></span>
                        <?php esc_html_e('HTML Description', 'carttrigger-bag'); ?>
                    </h3>
                    <div class="ctb-module-body">
                        <?php
                        $description_raw = get_term_field('description', $term->term_id, 'product_brand', 'raw');
                        wp_editor(
                            html_entity_decode((string) $description_raw, ENT_QUOTES, 'UTF-8'),
                            'ctbag_brand_description',
                            [
                                'textarea_name' => 'ctbag_brand_description',
                                'textarea_rows' => 12,
                                'media_buttons' => true,
                                'teeny'         => false,
                            ]
                        );
                        ?>
                    </div>
                </div>
            </td>
        </tr>

        <!-- ── Custom Fields repeater ───────────────────────────────────────── -->
        <tr class="ctb-module-row">
            <td colspan="2">
                <div class="ctb-module">
                    <h3 class="ctb-module-title">
                        <span class="dashicons dashicons-editor-ul"></span>
                        <?php esc_html_e('Custom Fields', 'carttrigger-bag'); ?>
                    </h3>
                    <div class="ctb-module-body">
                        <div id="ctb-custom-fields">
                            <?php foreach ($custom_fields as $i => $field) : ?>
                                <div class="ctb-field-row">
                                    <span class="ctb-drag-handle dashicons dashicons-move"></span>
                                    <input type="text"
                                        name="ctbag_custom_fields[<?php echo (int) $i ?>][key]"
                                        value="<?php echo esc_attr($field['key']) ?>"
                                        placeholder="<?php esc_attr_e('Label', 'carttrigger-bag'); ?>"
                                        class="ctb-field-key" />
                                    <input type="text"
                                        name="ctbag_custom_fields[<?php echo (int) $i ?>][value]"
                                        value="<?php echo esc_attr($field['value']) ?>"
                                        placeholder="<?php esc_attr_e('Value', 'carttrigger-bag'); ?>"
                                        class="ctb-field-value" />
                                    <button type="button" class="ctb-remove-row button-link">&times;</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" id="ctb-add-field" class="button button-secondary">
                            <?php esc_html_e('+ Add field', 'carttrigger-bag'); ?>
                        </button>
                        <details>
                            <summary>
                                <?php esc_html_e('HTML structure and customisable classes', 'carttrigger-bag'); ?>
                            </summary>
                            <div style="margin-top:8px;padding:10px 12px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:3px;font-size:12px;line-height:1.8;">
                                <p style="margin:0 0 8px;font-weight:600;"><?php esc_html_e('Generated structure:', 'carttrigger-bag'); ?></p>
                                <pre style="margin:0 0 12px;overflow-x:auto;font-size:11px;background:#fff;padding:8px;border:1px solid #dcdcde;">&lt;dl class="<em>wrapper_class</em>"&gt;
  &lt;div&gt;
    &lt;dt class="<em>dt_class</em>"&gt;Etiqueta&lt;/dt&gt;
    &lt;dd class="<em>dd_class</em>"&gt;Valor&lt;/dd&gt;
  &lt;/div&gt;
&lt;/dl&gt;</pre>
                                <p style="margin:0 0 6px;font-weight:600;"><?php esc_html_e('Shortcode attributes (with defaults):', 'carttrigger-bag'); ?></p>
                                <pre style="margin:0;overflow-x:auto;font-size:11px;background:#fff;padding:8px;border:1px solid #dcdcde;">[ctbag_custom_fields
  brand="slug"
  wrapper_class="grid grid-cols-2 sm:grid-cols-3 gap-x-8 gap-y-4 max-w-2xl"
  dt_class="font-grotesk text-xs font-semibold uppercase tracking-widest text-blu-800/40 mb-1"
  dd_class="font-inter text-sm text-blu-900"
]</pre>
                                <p style="margin:12px 0 6px;font-weight:600;"><?php esc_html_e('Usage from PHP template (recommended):', 'carttrigger-bag'); ?></p>
                                <p style="margin:0 0 6px;color:#646970;"><?php esc_html_e('The helper function bypasses the WP shortcode parser: Tailwind arbitrary-value classes (text-[11px]) work without any escaping.', 'carttrigger-bag'); ?></p>
                                <pre style="margin:0 0 8px;overflow-x:auto;font-size:11px;background:#fff;padding:8px;border:1px solid #dcdcde;">echo ctb_custom_fields([
  'wrapper_class' => 'grid grid-cols-2 sm:grid-cols-3 gap-x-8 gap-y-4 max-w-2xl',
  'dt_class'      => 'font-grotesk text-xs font-semibold uppercase tracking-widest text-blu-800/40 mb-1',
  'dd_class'      => 'font-inter text-sm text-blu-900',
]);</pre>
                            </div>
                        </details>
                    </div>
                </div>
            </td>
        </tr>

        <!-- ── Awards repeater ──────────────────────────────────────────────── -->
        <tr class="ctb-module-row">
            <td colspan="2">
                <div class="ctb-module">
                    <h3 class="ctb-module-title">
                        <span class="dashicons dashicons-star-filled"></span>
                        <?php esc_html_e('Awards', 'carttrigger-bag'); ?>
                    </h3>
                    <div class="ctb-module-body">
                        <div id="ctb-awards">
                            <?php foreach ($awards as $i => $award) :
                                $logo_url = $award['logo_id'] ? wp_get_attachment_image_url((int) $award['logo_id'], 'thumbnail') : '';
                            ?>
                                <div class="ctb-award-row">
                                    <span class="ctb-drag-handle dashicons dashicons-move"></span>
                                    <div class="ctb-award-logo">
                                        <img src="<?php echo esc_url((string) $logo_url) ?>"
                                            class="ctb-logo-preview<?php echo $logo_url ? '' : ' hidden' ?>"
                                            alt="" />
                                        <input type="hidden"
                                            name="ctbag_awards[<?php echo (int) $i ?>][logo_id]"
                                            value="<?php echo (int) $award['logo_id'] ?>" />
                                        <button type="button" class="ctb-upload-logo button button-secondary">
                                            <?php esc_html_e('Logo', 'carttrigger-bag'); ?>
                                        </button>
                                    </div>
                                    <input type="text"
                                        name="ctbag_awards[<?php echo (int) $i ?>][name]"
                                        value="<?php echo esc_attr($award['name']) ?>"
                                        placeholder="<?php esc_attr_e('Award name', 'carttrigger-bag'); ?>"
                                        class="ctb-award-name" />
                                    <input type="text"
                                        name="ctbag_awards[<?php echo (int) $i ?>][event]"
                                        value="<?php echo esc_attr($award['event'] ?? '') ?>"
                                        placeholder="<?php esc_attr_e('Event (e.g. Vinum Extra 2024)', 'carttrigger-bag'); ?>"
                                        class="ctb-award-event" />
                                    <input type="number"
                                        name="ctbag_awards[<?php echo (int) $i ?>][year]"
                                        value="<?php echo esc_attr($award['year']) ?>"
                                        placeholder="<?php esc_attr_e('Year', 'carttrigger-bag'); ?>"
                                        class="ctb-award-year"
                                        min="1900" max="2100" />
                                    <button type="button" class="ctb-remove-row button-link">&times;</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" id="ctb-add-award" class="button button-secondary">
                            <?php esc_html_e('+ Add award', 'carttrigger-bag'); ?>
                        </button>
                        <details>
                            <summary>
                                <?php esc_html_e('HTML structure and customisable classes', 'carttrigger-bag'); ?>
                            </summary>
                            <div style="margin-top:8px;padding:10px 12px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:3px;font-size:12px;line-height:1.8;">
                                <p style="margin:0 0 8px;font-weight:600;"><?php esc_html_e('Generated structure:', 'carttrigger-bag'); ?></p>
                                <pre style="margin:0 0 12px;overflow-x:auto;font-size:11px;background:#fff;padding:8px;border:1px solid #dcdcde;">&lt;span class="<em>title_class</em>"&gt;Awards&lt;/span&gt;   &lt;!-- if title != "" --&gt;
&lt;div class="<em>line_class</em>"&gt;&lt;/div&gt;             &lt;!-- if line_class != "" --&gt;
&lt;div class="<em>wrapper_class</em>"&gt;
  &lt;div class="<em>card_class</em>"&gt;
    &lt;img class="<em>img_class</em>" /&gt;             &lt;!-- if logo present --&gt;
    &lt;div&gt;
      &lt;span class="<em>name_class</em>"&gt;Award name&lt;/span&gt;
      &lt;span class="<em>event_class</em>"&gt;Event&lt;/span&gt;       &lt;!-- if present --&gt;
      &lt;span class="<em>year_class</em>"&gt;2024&lt;/span&gt;        &lt;!-- if present --&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/div&gt;</pre>
                                <p style="margin:0 0 6px;font-weight:600;"><?php esc_html_e('Shortcode attributes (with defaults):', 'carttrigger-bag'); ?></p>
                                <pre style="margin:0 0 12px;overflow-x:auto;font-size:11px;background:#fff;padding:8px;border:1px solid #dcdcde;">[ctbag_awards
  brand="slug"
  title="Awards"
  title_class="editorial-label block mb-6"
  line_class="line-deco w-20 mb-10"
  wrapper_class="flex flex-col divide-y divide-blu-900/8 max-w-2xl"
  card_class="flex items-start gap-4 py-5"
  img_class="w-10 h-10 object-contain shrink-0 opacity-70 mt-0.5"
  name_class="font-grotesk text-sm text-blu-900 leading-snug"
  event_class="font-inter text-xs text-blu-800/60 mt-1"
  year_class="font-mono-accent text-&#91;10px&#93; text-blu-800/40 block mt-1"
]</pre>
                                <p style="margin:12px 0 6px;font-weight:600;"><?php esc_html_e('Usage from PHP template (recommended):', 'carttrigger-bag'); ?></p>
                                <p style="margin:0 0 6px;color:#646970;"><?php esc_html_e('The helper function bypasses the WP parser: text-[11px] classes work without escaping and the title is translated with your theme text domain.', 'carttrigger-bag'); ?></p>
                                <pre style="margin:0 0 8px;overflow-x:auto;font-size:11px;background:#fff;padding:8px;border:1px solid #dcdcde;">echo ctb_awards([
  'title'         => __('Awards', 'your-textdomain'),
  'wrapper_class' => 'flex flex-col divide-y divide-blu-900/8 max-w-2xl',
  'card_class'    => 'flex items-start gap-4 py-5',
  'img_class'     => 'w-10 h-10 object-contain shrink-0 opacity-70 mt-0.5',
  'name_class'    => 'font-grotesk text-[11px] font-semibold text-blu-900',
  'event_class'   => 'font-inter text-[10px] text-blu-800/60 mt-1',
  'year_class'    => 'font-mono-accent text-[10px] text-blu-800/40 block mt-1',
]);</pre>
                            </div>
                        </details>
                    </div>
                </div>
            </td>
        </tr>

        <!-- ── Gallery ──────────────────────────────────────────────────────── -->
        <tr class="ctb-module-row">
            <td colspan="2">
                <div class="ctb-module">
                    <h3 class="ctb-module-title">
                        <span class="dashicons dashicons-format-gallery"></span>
                        <?php esc_html_e('Gallery', 'carttrigger-bag'); ?>
                    </h3>
                    <div class="ctb-module-body">
                        <input type="hidden" id="ctbag_gallery" name="ctbag_gallery"
                            value="<?php echo esc_attr($gallery_ids) ?>" />
                        <div id="ctb-gallery-preview" class="ctb-gallery-thumbnails">
                            <?php
                            if ($gallery_ids) {
                                foreach (explode(',', $gallery_ids) as $img_id) {
                                    $img_id = (int) $img_id;
                                    if ($img_id) {
                                        echo '<div class="ctb-gallery-item">';
                                        echo wp_get_attachment_image($img_id, 'thumbnail', false, ['class' => 'ctb-gallery-thumb', 'data-id' => $img_id]);
                                        echo '<button type="button" class="ctb-gallery-remove" aria-label="' . esc_attr__('Remove', 'carttrigger-bag') . '">&times;</button>';
                                        echo '</div>';
                                    }
                                }
                            }
                            ?>
                        </div>
                        <button type="button" id="ctb-manage-gallery" class="button button-secondary">
                            <?php esc_html_e('Manage gallery', 'carttrigger-bag'); ?>
                        </button>
                        <details>
                            <summary>
                                <?php esc_html_e('HTML structure and customisable classes', 'carttrigger-bag'); ?>
                            </summary>
                            <div style="margin-top:8px;padding:10px 12px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:3px;font-size:12px;line-height:1.8;">
                                <p style="margin:0 0 8px;font-weight:600;"><?php esc_html_e('Generated structure:', 'carttrigger-bag'); ?></p>
                                <pre style="margin:0 0 12px;overflow-x:auto;font-size:11px;background:#fff;padding:8px;border:1px solid #dcdcde;">&lt;span class="<em>title_class</em>"&gt;Gallery&lt;/span&gt;  &lt;!-- if title != "" --&gt;
&lt;div class="<em>line_class</em>"&gt;&lt;/div&gt;            &lt;!-- if line_class != "" --&gt;
&lt;div class="<em>wrapper_class</em>"&gt;
  &lt;!-- without lightbox --&gt;
  &lt;a class="<em>item_class</em>" href="full-url" target="_blank"&gt;
    &lt;img class="<em>img_class</em>" /&gt;
  &lt;/a&gt;
  &lt;!-- with lightbox="1" --&gt;
  &lt;a class="<em>item_class</em>" href="full-url"
     data-ctb-lb-src="full-url" data-ctb-lb-w="800" data-ctb-lb-h="600"&gt;
    &lt;img class="<em>img_class</em>" /&gt;
  &lt;/a&gt;
&lt;/div&gt;</pre>
                                <p style="margin:0 0 4px;font-weight:600;"><?php esc_html_e('Native WooCommerce lightbox (PhotoSwipe):', 'carttrigger-bag'); ?></p>
                                <p style="margin:0 0 8px;color:#646970;"><?php esc_html_e('Add lightbox="1" to open images in the native WooCommerce lightbox. Without this attribute images open in a new tab (default behaviour).', 'carttrigger-bag'); ?></p>
                                <p style="margin:0 0 6px;font-weight:600;"><?php esc_html_e('Shortcode attributes (with defaults):', 'carttrigger-bag'); ?></p>
                                <pre style="margin:0 0 12px;overflow-x:auto;font-size:11px;background:#fff;padding:8px;border:1px solid #dcdcde;">[ctbag_gallery
  brand="slug"
  title="Gallery"
  title_class="editorial-label block mb-6"
  line_class="line-deco w-20 mb-10"
  wrapper_class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3"
  item_class="block aspect-square overflow-hidden group"
  img_class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
  size_thumb="medium_large"
  size_full="large"
  lightbox=""    <!-- "1" to enable WooCommerce lightbox -->
]</pre>
                                <p style="margin:12px 0 6px;font-weight:600;"><?php esc_html_e('Usage from PHP template (recommended):', 'carttrigger-bag'); ?></p>
                                <p style="margin:0 0 6px;color:#646970;"><?php esc_html_e('The helper function bypasses the WP parser: text-[11px] classes work without escaping and the title is translated with your theme text domain.', 'carttrigger-bag'); ?></p>
                                <pre style="margin:0 0 8px;overflow-x:auto;font-size:11px;background:#fff;padding:8px;border:1px solid #dcdcde;">echo ctb_gallery([
  'title'         => __('Galería', 'your-textdomain'),
  'wrapper_class' => 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3',
  'item_class'    => 'block aspect-square overflow-hidden group',
  'img_class'     => 'w-full h-full object-cover transition-transform duration-500 group-hover:scale-105',
  'size_thumb'    => 'medium_large',
  'size_full'     => 'large',
  'lightbox'      => '1',  // '' = new tab (default) | '1' = WooCommerce lightbox
]);</pre>
                            </div>
                        </details>
                    </div>
                </div>
            </td>
        </tr>

    <?php
    }

    /**
     * Fields shown on the "Add Brand" screen (no term_id yet — no meta read).
     */
    public function ctb_add_form_fields($taxonomy)
    {
    ?>

        <!-- ── Plugin Banner ────────────────────────────────────────────────── -->
        <div class="ctb-plugin-banner" style="margin:8px 0 16px;">
            <?php wp_nonce_field('ctbag_save_term_meta', 'ctbag_nonce'); ?>
            <div class="ctb-plugin-header">
                <strong class="ctb-plugin-name">CartTrigger – BAG</strong>
                <span class="ctb-plugin-version">v<?php echo esc_html( CTBAG_VERSION ); ?></span>
            </div>
            <p class="ctb-plugin-tagline">Brand · Awards · Gallery for WooCommerce</p>
        </div>

        <!-- ── TinyMCE Description ──────────────────────────────────────────── -->
        <div class="form-field">
            <div class="ctb-module">
                <h3 class="ctb-module-title">
                    <span class="dashicons dashicons-edit"></span>
                    <?php esc_html_e('HTML Description', 'carttrigger-bag'); ?>
                </h3>
                <div class="ctb-module-body">
                    <?php
                    wp_editor(
                        '',
                        'ctbag_brand_description',
                        [
                            'textarea_name' => 'ctbag_brand_description',
                            'textarea_rows' => 12,
                            'media_buttons' => true,
                            'teeny'         => false,
                        ]
                    );
                    ?>
                </div>
            </div>
        </div>

        <!-- ── Custom Fields ────────────────────────────────────────────────── -->
        <div class="form-field">
            <div class="ctb-module">
                <h3 class="ctb-module-title">
                    <span class="dashicons dashicons-editor-ul"></span>
                    <?php esc_html_e('Custom Fields', 'carttrigger-bag'); ?>
                </h3>
                <div class="ctb-module-body">
                    <div id="ctb-custom-fields"></div>
                    <button type="button" id="ctb-add-field" class="button button-secondary">
                        <?php esc_html_e('+ Add field', 'carttrigger-bag'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Awards ───────────────────────────────────────────────────────── -->
        <div class="form-field">
            <div class="ctb-module">
                <h3 class="ctb-module-title">
                    <span class="dashicons dashicons-star-filled"></span>
                    <?php esc_html_e('Awards', 'carttrigger-bag'); ?>
                </h3>
                <div class="ctb-module-body">
                    <div id="ctb-awards"></div>
                    <button type="button" id="ctb-add-award" class="button button-secondary">
                        <?php esc_html_e('+ Add award', 'carttrigger-bag'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Gallery ──────────────────────────────────────────────────────── -->
        <div class="form-field">
            <div class="ctb-module">
                <h3 class="ctb-module-title">
                    <span class="dashicons dashicons-format-gallery"></span>
                    <?php esc_html_e('Gallery', 'carttrigger-bag'); ?>
                </h3>
                <div class="ctb-module-body">
                    <input type="hidden" id="ctbag_gallery" name="ctbag_gallery" value="" />
                    <div id="ctb-gallery-preview" class="ctb-gallery-thumbnails"></div>
                    <button type="button" id="ctb-manage-gallery" class="button button-secondary">
                        <?php esc_html_e('Manage gallery', 'carttrigger-bag'); ?>
                    </button>
                </div>
            </div>
        </div>

    <?php
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 4. SAVE META
    // ═══════════════════════════════════════════════════════════════════════════

    public function ctbag_save_term_meta($term_id, $tt_id)
    {
        // Guard against infinite loop: saving the description via wp_update_term()
        // re-fires edited_product_brand, which would call this function again.
        static $is_saving = false;
        if ($is_saving) {
            return;
        }

        if (! isset($_POST['ctbag_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ctbag_nonce'])), 'ctbag_save_term_meta')) {
            return;
        }
        if (! current_user_can('manage_categories')) {
            return;
        }

        // ── Description via TinyMCE ───────────────────────────────────────────
        if (isset($_POST['ctbag_brand_description'])) {
            $description = wp_kses_post(wp_unslash($_POST['ctbag_brand_description']));
            $is_saving   = true;
            wp_update_term((int) $term_id, 'product_brand', ['description' => $description]);
            $is_saving   = false;
        }

        // ── Custom Fields ─────────────────────────────────────────────────────
        // wp_strip_all_tags() is used instead of sanitize_text_field() because
        // sanitize_text_field() calls wp_check_invalid_utf8() which on some server
        // configurations returns '' for valid accented characters / typographic quotes.
        $raw_fields = isset($_POST['ctbag_custom_fields']) ? (array) wp_unslash( $_POST['ctbag_custom_fields'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each element is sanitized via wp_strip_all_tags() inside the foreach loop below
        $fields     = [];
        foreach ($raw_fields as $field) {
            $key   = trim(wp_strip_all_tags(wp_unslash($field['key']   ?? '')));
            $value = trim(wp_strip_all_tags(wp_unslash($field['value'] ?? '')));
            if ($key !== '') {
                $fields[] = ['key' => $key, 'value' => $value];
            }
        }
        // Store as a PHP array: WordPress uses maybe_serialize() which calls
        // serialize() — no backslash escaping at all, immune to wp_unslash()
        // stripping backslashes inside JSON strings (which broke double-quote chars).
        update_term_meta((int) $term_id, 'ctbag_custom_fields', $fields);

        // ── Awards ────────────────────────────────────────────────────────────
        $raw_awards = isset($_POST['ctbag_awards']) ? (array) wp_unslash( $_POST['ctbag_awards'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each element is sanitized via wp_strip_all_tags()/sanitize_text_field() inside the foreach loop below
        $awards     = [];
        foreach ($raw_awards as $award) {
            $logo_id = (int) ($award['logo_id'] ?? 0);
            $name    = trim(wp_strip_all_tags(wp_unslash($award['name']  ?? '')));
            $event   = trim(wp_strip_all_tags(wp_unslash($award['event'] ?? '')));
            $year    = sanitize_text_field(wp_unslash($award['year']  ?? '')); // numeric/ASCII only
            if ($name !== '') {
                $awards[] = ['logo_id' => $logo_id, 'name' => $name, 'event' => $event, 'year' => $year];
            }
        }
        update_term_meta((int) $term_id, 'ctbag_awards', $awards);

        // ── Gallery ───────────────────────────────────────────────────────────
        $gallery_raw = isset($_POST['ctbag_gallery']) ? sanitize_text_field(wp_unslash($_POST['ctbag_gallery'])) : '';
        $gallery_ids = implode(',', array_filter(array_map('intval', explode(',', $gallery_raw))));
        update_term_meta((int) $term_id, 'ctbag_gallery', $gallery_ids);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 5. FRONTEND: BRAND INFO ON SINGLE PRODUCT
    // ═══════════════════════════════════════════════════════════════════════════

    public function ctb_display_brand_info()
    {
        global $product;
        $terms = get_the_terms($product->get_id(), 'product_brand');
        if (empty($terms) || is_wp_error($terms)) {
            return;
        }

        foreach ($terms as $term) {
            $thumb_url = function_exists('wc_get_brand_thumbnail_url')
                ? wc_get_brand_thumbnail_url($term->term_id, 'thumbnail')
                : '';

            echo '<div class="ctb-brand-container">';
            if ($thumb_url) {
                printf(
                    '<a href="%s" class="ctb-brand-logo-link"><img src="%s" alt="%s" class="ctb-brand-logo" loading="lazy" /></a>',
                    esc_url(get_term_link($term)),
                    esc_url($thumb_url),
                    esc_attr($term->name)
                );
            } else {
                printf(
                    '<a href="%s" class="ctb-brand-name">%s</a>',
                    esc_url(get_term_link($term)),
                    esc_html($term->name)
                );
            }
            echo '</div>';
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 6. FRONTEND: SHORTCODE [ctbag_awards]
    // ═══════════════════════════════════════════════════════════════════════════

    public function ctb_awards_shortcode($atts)
    {
        $atts = shortcode_atts(
            [
                'brand'        => '',
                'title'        => __('Awards', 'carttrigger-bag'),
                'title_class'  => 'editorial-label block mb-6',
                'line_class'   => 'line-deco w-20 mb-10',
                'wrapper_class' => 'flex flex-col divide-y divide-blu-900/8 max-w-2xl',
                'card_class'   => 'flex items-start gap-4 py-5',
                'img_class'    => 'w-10 h-10 object-contain shrink-0 opacity-70 mt-0.5',
                'name_class'   => 'font-grotesk text-sm text-blu-900 leading-snug',
                'event_class'  => 'font-inter text-xs text-blu-800/60 mt-1',
                'year_class'   => 'font-mono-accent text-xs text-blu-800/40 block mt-1',
            ],
            $atts,
            'ctbag_awards'
        );
        $atts = $this->ctb_decode_classes($atts);

        $term = $this->ctb_resolve_term($atts['brand']);
        if (! $term) {
            return '';
        }

        $awards = $this->ctb_get_awards($term->term_id);
        if (empty($awards)) {
            return '';
        }

        ob_start();
    ?>
        <?php if ($atts['title'] !== '') : ?>
            <span class="<?php echo esc_attr($atts['title_class']) ?>"><?php echo esc_html($atts['title']) ?></span>
        <?php endif; ?>
        <?php if ($atts['line_class'] !== '') : ?>
            <div class="<?php echo esc_attr($atts['line_class']) ?>"></div>
        <?php endif; ?>
        <div class="<?php echo esc_attr($atts['wrapper_class']) ?>">
            <?php foreach ($awards as $award) :
                $logo_url = ! empty($award['logo_id']) ? wp_get_attachment_image_url((int) $award['logo_id'], 'medium') : '';
            ?>
                <div class="<?php echo esc_attr($atts['card_class']) ?>">
                    <?php if ($logo_url) : ?>
                        <img src="<?php echo esc_url($logo_url) ?>"
                            alt="<?php echo esc_attr($award['name']) ?>"
                            class="<?php echo esc_attr($atts['img_class']) ?>"
                            loading="lazy" />
                    <?php endif; ?>
                    <div class="flex flex-col items-center">
                        <span class="<?php echo esc_attr($atts['name_class']) ?>"><?php echo esc_html($award['name']) ?></span>
                        <?php if (! empty($award['event'])) : ?>
                            <span class="<?php echo esc_attr($atts['event_class']) ?> block"><?php echo esc_html($award['event']) ?></span>
                        <?php endif; ?>
                        <?php if (! empty($award['year'])) : ?>
                            <span class="<?php echo esc_attr($atts['year_class']) ?>"><?php echo esc_html($award['year']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php
        return ob_get_clean();
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 6b. SHORTCODE [ctbag_custom_fields]
    //
    // Usage:
    //   [ctbag_custom_fields]                        ← auto-detect brand on tax page
    //   [ctbag_custom_fields brand="slug"]           ← explicit brand
    //   [ctbag_custom_fields wrapper_class="..." dt_class="..." dd_class="..."]
    // ═══════════════════════════════════════════════════════════════════════════

    public function ctb_custom_fields_shortcode($atts)
    {
        $atts = shortcode_atts(
            [
                'brand'         => '',
                'wrapper_class' => 'grid grid-cols-2 sm:grid-cols-3 gap-x-8 gap-y-4 max-w-2xl',
                'dt_class'      => 'font-grotesk text-xs font-semibold uppercase tracking-widest text-blu-800/40 mb-1',
                'dd_class'      => 'font-inter text-sm text-blu-900',
            ],
            $atts,
            'ctbag_custom_fields'
        );
        $atts = $this->ctb_decode_classes($atts);

        $term = $this->ctb_resolve_term($atts['brand']);
        if (! $term) {
            return '';
        }

        $fields = $this->ctb_get_custom_fields($term->term_id);
        if (empty($fields)) {
            return '';
        }

        ob_start();
    ?>
        <dl class="<?php echo esc_attr($atts['wrapper_class']) ?>">
            <?php foreach ($fields as $cf) :
                if (empty($cf['key'])) continue; ?>
                <div>
                    <dt class="<?php echo esc_attr($atts['dt_class']) ?>"><?php echo esc_html($cf['key']) ?></dt>
                    <dd class="<?php echo esc_attr($atts['dd_class']) ?>"><?php echo esc_html($cf['value']) ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    <?php
        return ob_get_clean();
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 6c. SHORTCODE [ctbag_gallery]
    //
    // Usage:
    //   [ctbag_gallery]                              ← auto-detect brand on tax page
    //   [ctbag_gallery brand="slug"]                 ← explicit brand
    //   [ctbag_gallery wrapper_class="..." item_class="..." img_class="..."]
    // ═══════════════════════════════════════════════════════════════════════════

    public function ctb_gallery_shortcode($atts)
    {
        $atts = shortcode_atts(
            [
                'brand'         => '',
                'title'         => __('Gallery', 'carttrigger-bag'),
                'title_class'   => 'editorial-label block mb-6',
                'line_class'    => 'line-deco w-20 mb-10',
                'wrapper_class' => 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3',
                'item_class'    => 'block aspect-square overflow-hidden group',
                'img_class'     => 'w-full h-full object-cover transition-transform duration-500 group-hover:scale-105',
                'size_thumb'    => 'medium_large',
                'size_full'     => 'large',
                'lightbox'      => '',  // '1' | 'wc'  → use WooCommerce PhotoSwipe lightbox
            ],
            $atts,
            'ctbag_gallery'
        );
        $atts = $this->ctb_decode_classes($atts);

        $term = $this->ctb_resolve_term($atts['brand']);
        if (! $term) {
            return '';
        }

        $gallery_raw = $this->ctb_get_gallery_ids($term->term_id);
        if (! $gallery_raw) {
            return '';
        }

        $ids = array_filter(array_map('intval', explode(',', $gallery_raw)));
        if (empty($ids)) {
            return '';
        }

        $use_lb = ! empty($atts['lightbox']);
        if ($use_lb) {
            $this->ctb_maybe_enqueue_gallery_lightbox();
        }

        $wrapper_class = trim($atts['wrapper_class'] . ($use_lb ? ' ctb-gallery--lb' : ''));

        ob_start();
    ?>
        <?php if ($atts['title'] !== '') : ?>
            <span class="<?php echo esc_attr($atts['title_class']) ?>"><?php echo esc_html($atts['title']) ?></span>
        <?php endif; ?>
        <?php if ($atts['line_class'] !== '') : ?>
            <div class="<?php echo esc_attr($atts['line_class']) ?>"></div>
        <?php endif; ?>
        <div class="<?php echo esc_attr($wrapper_class) ?>">
            <?php foreach ($ids as $img_id) :
                $full_src  = wp_get_attachment_image_src($img_id, $atts['size_full']);
                if (! $full_src) continue;
                [$full_url, $full_w, $full_h] = $full_src;
                $thumb_url = wp_get_attachment_image_url($img_id, $atts['size_thumb']) ?: $full_url;
                $alt       = get_post_meta($img_id, '_wp_attachment_image_alt', true) ?: '';
            ?>
                <?php if ($use_lb) : ?>
                <a href="<?php echo esc_url($full_url) ?>"
                    class="<?php echo esc_attr($atts['item_class']) ?>"
                    target="_blank"
                    rel="noopener"
                    data-ctb-lb-src="<?php echo esc_url($full_url) ?>"
                    data-ctb-lb-w="<?php echo (int) $full_w ?>"
                    data-ctb-lb-h="<?php echo (int) $full_h ?>">
                <?php else : ?>
                <a href="<?php echo esc_url($full_url) ?>"
                    class="<?php echo esc_attr($atts['item_class']) ?>"
                    target="_blank"
                    rel="noopener">
                <?php endif; ?>
                    <img src="<?php echo esc_url($thumb_url) ?>"
                        alt="<?php echo esc_attr($alt) ?>"
                        class="<?php echo esc_attr($atts['img_class']) ?>"
                        loading="lazy" />
                </a>
            <?php endforeach; ?>
        </div>
<?php
        return ob_get_clean();
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 7. FRONTEND: PUBLIC ASSETS
    // ═══════════════════════════════════════════════════════════════════════════

    public function ctb_enqueue_assets()
    {
        if (! function_exists('WC')) {
            return;
        }

        // WC registers PhotoSwipe only on single-product pages.
        // On brand taxonomy pages (and anywhere else the gallery lightbox may be used)
        // we register it ourselves from WC's own bundled files, so it is always available.
        if (! wp_script_is('photoswipe', 'registered')) {
            wp_register_script(
                'photoswipe',
                WC()->plugin_url() . '/assets/js/photoswipe/photoswipe.min.js',
                [],
                WC_VERSION,
                true
            );
        }
        if (! wp_script_is('photoswipe-ui-default', 'registered')) {
            wp_register_script(
                'photoswipe-ui-default',
                WC()->plugin_url() . '/assets/js/photoswipe/photoswipe-ui-default.min.js',
                ['photoswipe'],
                WC_VERSION,
                true
            );
        }
        if (! wp_style_is('photoswipe', 'registered')) {
            wp_register_style(
                'photoswipe',
                WC()->plugin_url() . '/assets/css/photoswipe/photoswipe.min.css',
                [],
                WC_VERSION
            );
        }
        if (! wp_style_is('photoswipe-default-skin', 'registered')) {
            wp_register_style(
                'photoswipe-default-skin',
                WC()->plugin_url() . '/assets/css/photoswipe/default-skin/default-skin.min.css',
                ['photoswipe'],
                WC_VERSION
            );
        }

        // Enqueue PhotoSwipe CSS/JS only on brand taxonomy pages that actually have
        // a gallery with lightbox. Assets are registered above so shortcode output
        // on other contexts can still enqueue them lazily if needed.
        if (is_tax('product_brand')) {
            $term = get_queried_object();
            if ($term && ! is_wp_error($term) && get_term_meta($term->term_id, 'ctbag_gallery', true)) {
                wp_enqueue_style('photoswipe');
                wp_enqueue_style('photoswipe-default-skin');
                wp_enqueue_script('wc-photoswipe');
                wp_enqueue_script('wc-photoswipe-ui-default');
            }
        }
    }

    /**
     * Enqueue PhotoSwipe JS + register the footer output — called once per page
     * the first time a [ctbag_gallery lightbox="1"] shortcode renders.
     */
    private function ctb_maybe_enqueue_gallery_lightbox()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        wp_enqueue_script(
            'ctbag-gallery-lightbox',
            CTBAG_URL . 'assets/js/ctbag-gallery-lightbox.js',
            ['wc-photoswipe', 'wc-photoswipe-ui-default'],
            CTBAG_VERSION,
            true
        );
        add_action('wp_footer', [$this, 'ctb_gallery_lightbox_footer'], 20);
    }

    /**
     * Output the PhotoSwipe DOM container (only on non-product pages — WC injects
     * it on single-product via its own template) and the gallery init script.
     */
    public function ctb_gallery_lightbox_footer()
    {
        if (! is_singular('product')) :
?>
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="pswp__bg"></div>
    <div class="pswp__scroll-wrap">
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>
        <div class="pswp__ui pswp__ui--hidden">
            <div class="pswp__top-bar">
                <div class="pswp__counter"></div>
                <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
                <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                        <div class="pswp__rot-icon"><div class="pswp__double-circle"></div></div>
                    </div>
                </div>
            </div>
            <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button>
            <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>
            <div class="pswp__caption"><div class="pswp__caption__center"></div></div>
        </div>
    </div>
</div>
<?php
        endif;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Decode HTML entities in shortcode class attributes.
     *
     * WordPress' shortcode parser stops at the first bare `]`, so Tailwind
     * arbitrary values like `text-[11px]` break shortcodes when typed in the
     * WP editor. The workaround is to write `text-&#91;11px&#93;` in the editor;
     * this helper restores the original characters before outputting to HTML.
     *
     * Usage in every shortcode handler: $atts = $this->ctb_decode_classes($atts);
     *
     * @param array $atts  Shortcode attributes array.
     * @return array       Same array with class values decoded.
     */
    private function ctb_decode_classes(array $atts): array
    {
        foreach ($atts as $key => $value) {
            if (str_ends_with($key, '_class') && is_string($value)) {
                $atts[$key] = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }
        return $atts;
    }

    /**
     * Resolve a WP_Term from a brand slug (or from the current tax query if empty).
     *
     * @param string $brand_slug  Optional slug; empty = auto-detect.
     * @return WP_Term|false
     */
    private function ctb_resolve_term($brand_slug)
    {
        if ($brand_slug) {
            $term = get_term_by('slug', sanitize_title($brand_slug), 'product_brand');
            return ($term && ! is_wp_error($term)) ? $term : false;
        }
        if (is_tax('product_brand')) {
            $term = get_queried_object();
            return ($term instanceof WP_Term) ? $term : false;
        }
        return false;
    }

    private function ctb_get_custom_fields($term_id)
    {
        $raw = get_term_meta((int) $term_id, 'ctbag_custom_fields', true);
        // New format: PHP array (stored via maybe_serialize).
        // Legacy format: JSON string (stored by earlier versions).
        if (is_array($raw)) {
            return $raw;
        }
        $data = json_decode((string) $raw, true);
        return is_array($data) ? $data : [];
    }

    private function ctb_get_awards($term_id)
    {
        $raw = get_term_meta((int) $term_id, 'ctbag_awards', true);
        // New format: PHP array (stored via maybe_serialize).
        // Legacy format: JSON string (stored by earlier versions).
        if (is_array($raw)) {
            return $raw;
        }
        $data = json_decode((string) $raw, true);
        return is_array($data) ? $data : [];
    }

    private function ctb_get_gallery_ids($term_id)
    {
        return get_term_meta((int) $term_id, 'ctbag_gallery', true) ?: '';
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 8. ACTIVATION NOTICE
    // ═══════════════════════════════════════════════════════════════════════════

    public function ctb_activation_notice()
    {
        if (! get_transient('ctbag_activated')) {
            return;
        }
        delete_transient('ctbag_activated');

        $url = admin_url('edit-tags.php?taxonomy=product_brand&post_type=product');
        printf(
            '<div class="notice notice-success is-dismissible"><p><strong>CartTrigger – BAG %s</strong> %s <a href="%s">%s &rarr;</a></p></div>',
            esc_html(CTBAG_VERSION),
            esc_html__('activated successfully.', 'carttrigger-bag'),
            esc_url($url),
            esc_html__('Manage brands', 'carttrigger-bag')
        );
    }
}

// On activation: set a transient so the admin notice fires once on next load.
register_activation_hook(__FILE__, function () {
    set_transient('ctbag_activated', 1, 30);
});

/**
 * One-time migration: move term meta from legacy ctb_* keys to ctbag_* keys.
 * Runs once per site; skipped if already done (ctbag_meta_migrated option set).
 */
function ctbag_migrate_meta_keys() {
    if ( get_option( 'ctbag_meta_migrated' ) ) {
        return;
    }

    $terms = get_terms( [ 'taxonomy' => 'product_brand', 'hide_empty' => false ] );
    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        update_option( 'ctbag_meta_migrated', 1 );
        return;
    }

    $map = [
        'ctb_custom_fields' => 'ctbag_custom_fields',
        'ctb_awards'        => 'ctbag_awards',
        'ctb_gallery'       => 'ctbag_gallery',
    ];

    foreach ( $terms as $term ) {
        foreach ( $map as $old_key => $new_key ) {
            $value = get_term_meta( $term->term_id, $old_key, true );
            if ( $value !== '' && $value !== false ) {
                update_term_meta( $term->term_id, $new_key, $value );
                delete_term_meta( $term->term_id, $old_key );
            }
        }
    }

    update_option( 'ctbag_meta_migrated', 1 );
}
add_action( 'init', 'ctbag_migrate_meta_keys' );

// Initialise the plugin and store instance for direct PHP template access.
global $ctbag_bag;
$ctbag_bag = new CTBAG_CartTrigger_BAG();

/**
 * PHP helper functions for use in theme templates.
 *
 * Calling these functions directly bypasses WordPress' shortcode string parser,
 * so Tailwind arbitrary-value classes like text-[11px] work without any escaping.
 *
 * Usage:
 *   echo ctb_awards(['title' => __('Awards','my-theme'), 'name_class' => 'text-[11px]']);
 *   echo ctb_custom_fields(['wrapper_class' => 'flex gap-4']);
 *   echo ctb_gallery(['title' => __('Gallery','my-theme')]);
 */
function ctbag_awards( array $atts = [] ): string {
    global $ctbag_bag;
    return $ctbag_bag ? $ctbag_bag->ctb_awards_shortcode( $atts ) : '';
}

function ctbag_custom_fields( array $atts = [] ): string {
    global $ctbag_bag;
    return $ctbag_bag ? $ctbag_bag->ctb_custom_fields_shortcode( $atts ) : '';
}

function ctbag_gallery( array $atts = [] ): string {
    global $ctbag_bag;
    return $ctbag_bag ? $ctbag_bag->ctb_gallery_shortcode( $atts ) : '';
}
