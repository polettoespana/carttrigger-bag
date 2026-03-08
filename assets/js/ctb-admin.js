/* CartTrigger BAG — Admin JS */
/* global ctbagAdmin, wp */
jQuery(function ($) {
    'use strict';

    // ── Index counters (start after existing rows) ────────────────────────────
    var fieldIndex = $('#ctb-custom-fields .ctb-field-row').length;
    var awardIndex = $('#ctb-awards .ctb-award-row').length;

    // ── Custom Fields repeater ────────────────────────────────────────────────
    $('#ctb-add-field').on('click', function () {
        var row = '<div class="ctb-field-row">'
            + '<span class="ctb-drag-handle dashicons dashicons-move"></span>'
            + '<input type="text" name="ctbag_custom_fields[' + fieldIndex + '][key]"'
            + ' placeholder="' + ctbagAdmin.labelPlaceholder + '" class="ctb-field-key" />'
            + '<input type="text" name="ctbag_custom_fields[' + fieldIndex + '][value]"'
            + ' placeholder="' + ctbagAdmin.valuePlaceholder + '" class="ctb-field-value" />'
            + '<button type="button" class="ctb-remove-row button-link">&times;</button>'
            + '</div>';
        $('#ctb-custom-fields').append(row);
        fieldIndex++;
    });

    // ── Custom Fields drag & drop reorder ─────────────────────────────────────
    $('#ctb-custom-fields').sortable({
        items:       '.ctb-field-row',
        handle:      '.ctb-drag-handle',
        cursor:      'grabbing',
        opacity:     0.7,
        placeholder: 'ctb-row-placeholder'
    });

    // ── Awards repeater ───────────────────────────────────────────────────────
    $('#ctb-add-award').on('click', function () {
        var row = '<div class="ctb-award-row">'
            + '<span class="ctb-drag-handle dashicons dashicons-move"></span>'
            + '<div class="ctb-award-logo">'
            + '<img class="ctb-logo-preview hidden" src="" alt="" />'
            + '<input type="hidden" name="ctbag_awards[' + awardIndex + '][logo_id]" value="0" />'
            + '<button type="button" class="ctb-upload-logo button button-secondary">Logo</button>'
            + '</div>'
            + '<input type="text" name="ctbag_awards[' + awardIndex + '][name]"'
            + ' placeholder="' + ctbagAdmin.awardNamePlaceholder + '" class="ctb-award-name" />'
            + '<input type="text" name="ctbag_awards[' + awardIndex + '][event]"'
            + ' placeholder="' + ctbagAdmin.awardEventPlaceholder + '" class="ctb-award-event" />'
            + '<input type="number" name="ctbag_awards[' + awardIndex + '][year]"'
            + ' placeholder="' + ctbagAdmin.awardYearPlaceholder + '" class="ctb-award-year" min="1900" max="2100" />'
            + '<button type="button" class="ctb-remove-row button-link">&times;</button>'
            + '</div>';
        $('#ctb-awards').append(row);
        awardIndex++;
    });

    // ── Awards drag & drop reorder ────────────────────────────────────────────
    $('#ctb-awards').sortable({
        items:       '.ctb-award-row',
        handle:      '.ctb-drag-handle',
        cursor:      'grabbing',
        opacity:     0.7,
        placeholder: 'ctb-row-placeholder'
    });

    // ── Remove row (delegated) ────────────────────────────────────────────────
    $(document).on('click', '.ctb-remove-row', function () {
        $(this).closest('.ctb-field-row, .ctb-award-row').remove();
    });

    // ── Award logo media uploader (delegated) ─────────────────────────────────
    $(document).on('click', '.ctb-upload-logo', function () {
        var $btn     = $(this);
        var $row     = $btn.closest('.ctb-award-row');
        var $preview = $row.find('.ctb-logo-preview');
        var $hidden  = $row.find('input[type="hidden"]');

        var frame = wp.media({
            title:    ctbagAdmin.mediaTitle,
            button:   { text: ctbagAdmin.mediaButton },
            multiple: false,
            library:  { type: 'image' }
        });

        frame.on('select', function () {
            var a   = frame.state().get('selection').first().toJSON();
            var src = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
            $hidden.val(a.id);
            $preview.attr('src', src).removeClass('hidden');
        });

        frame.open();
    });

    // ── Gallery uploader ──────────────────────────────────────────────────────
    var galleryFrame;

    $('#ctb-manage-gallery').on('click', function () {
        if (galleryFrame) {
            galleryFrame.open();
            return;
        }

        galleryFrame = wp.media({
            title:    ctbagAdmin.mediaTitle,
            button:   { text: ctbagAdmin.mediaButton },
            multiple: 'add',
            library:  { type: 'image' }
        });

        // Pre-select images already in the gallery
        galleryFrame.on('open', function () {
            var ids       = $('#ctbag_gallery').val().split(',').filter(Boolean);
            var selection = galleryFrame.state().get('selection');
            ids.forEach(function (id) {
                var attachment = wp.media.attachment(parseInt(id, 10));
                attachment.fetch();
                selection.add(attachment ? [attachment] : []);
            });
        });

        galleryFrame.on('select', function () {
            var selection = galleryFrame.state().get('selection');
            var ids  = [];
            var html = '';

            selection.each(function (attachment) {
                var a   = attachment.toJSON();
                var src = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
                ids.push(a.id);
                html += '<div class="ctb-gallery-item">'
                    + '<img src="' + src + '" class="ctb-gallery-thumb" alt="" data-id="' + a.id + '" />'
                    + '<button type="button" class="ctb-gallery-remove" aria-label="Remove">&times;</button>'
                    + '</div>';
            });

            $('#ctbag_gallery').val(ids.join(','));
            $('#ctb-gallery-preview').html(html);
        });

        galleryFrame.open();
    });

    // ── Gallery single-item removal ───────────────────────────────────────────
    $(document).on('click', '.ctb-gallery-remove', function () {
        $(this).closest('.ctb-gallery-item').remove();
        ctbSyncGalleryIds();
    });

    // ── Gallery drag & drop reorder ───────────────────────────────────────────
    $('#ctb-gallery-preview').sortable({
        items:       '.ctb-gallery-item',
        cursor:      'grabbing',
        opacity:     0.7,
        placeholder: 'ctb-gallery-placeholder',
        stop:        ctbSyncGalleryIds
    });

    function ctbSyncGalleryIds() {
        var ids = $('#ctb-gallery-preview .ctb-gallery-thumb').map(function () {
            return $(this).data('id');
        }).get();
        $('#ctbag_gallery').val(ids.join(','));
    }

    // ── Hide native description textarea ──────────────────────────────────────
    // Edit screen: WP renders a <tr> containing #description.
    // Add screen:  WP renders a <div class="form-field"> containing #tag-description.
    // We hide it so our TinyMCE module is the only description editor visible.
    var $nativeDescEdit = $('#description').closest('tr.form-field');
    if ($nativeDescEdit.length) {
        $nativeDescEdit.hide();
    }
    var $nativeDescAdd = $('#tag-description').closest('.form-field');
    if ($nativeDescAdd.length) {
        $nativeDescAdd.hide();
    }

    // ── Sync TinyMCE content to textarea before form submit ───────────────────
    // Needed so ctbag_brand_description is included in POST on the add-brand form.
    $('form#addtag').on('submit', function () {
        if (typeof tinyMCE !== 'undefined') {
            tinyMCE.triggerSave();
        }
    });
});
