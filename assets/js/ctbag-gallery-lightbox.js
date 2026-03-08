/* global PhotoSwipe, PhotoSwipeUI_Default */
(function () {
    'use strict';

    function ctbInitLightbox() {
        var galleries = document.querySelectorAll('.ctb-gallery--lb');
        if (!galleries.length) return;

        if (typeof PhotoSwipe === 'undefined' || typeof PhotoSwipeUI_Default === 'undefined') {
            console.warn(
                '[CTBAG Gallery] Lightbox no disponible: los scripts de WooCommerce (PhotoSwipe) ' +
                'no están cargados en esta página. ' +
                'Asegúrate de que los scripts de WooCommerce estén habilitados en esta página ' +
                'o elimina el atributo lightbox="1" del shortcode. ' +
                'Las imágenes se abrirán en nueva pestaña como fallback.'
            );
            return;
        }

        var pswpEl = document.querySelector('.pswp');
        if (!pswpEl) return;

        galleries.forEach(function (gallery) {
            var links = gallery.querySelectorAll('a[data-ctb-lb-src]');
            var items = Array.from(links).map(function (a) {
                return {
                    src: a.dataset.ctbLbSrc,
                    w:   parseInt(a.dataset.ctbLbW, 10) || 0,
                    h:   parseInt(a.dataset.ctbLbH, 10) || 0,
                };
            });
            links.forEach(function (a, i) {
                a.addEventListener('click', function (e) {
                    e.preventDefault();
                    var ps = new PhotoSwipe(pswpEl, PhotoSwipeUI_Default, items, {
                        index:           i,
                        bgOpacity:       0.9,
                        showHideOpacity: true,
                        history:         false,
                    });
                    ps.init();
                });
            });
        });
    }

    // wc-photoswipe uses strategy:'defer' — deferred scripts execute before
    // DOMContentLoaded but after synchronous inline scripts like this one.
    // Waiting for DOMContentLoaded guarantees PhotoSwipe globals are defined.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ctbInitLightbox);
    } else {
        ctbInitLightbox();
    }
}());
