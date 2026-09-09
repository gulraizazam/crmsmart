(function ($) {
    'use strict';

    function markActiveFromUrl() {
        var $menu = $('#layout-menu');
        if (!$menu.length) {
            return;
        }

        var path = window.location.pathname.replace(/\/+$/, '') || '/';
        var $links = $menu.find('a.menu-link[href]').filter(function () {
            var href = this.getAttribute('href') || '';
            return href && href.indexOf('javascript:') !== 0 && href !== '#';
        });

        var best = null;
        var bestLen = -1;
        $links.each(function () {
            var hrefPath;
            try {
                hrefPath = new URL(this.href, window.location.origin).pathname.replace(/\/+$/, '') || '/';
            } catch (e) {
                return;
            }
            if (path === hrefPath || (hrefPath !== '/' && path.indexOf(hrefPath + '/') === 0)) {
                if (hrefPath.length > bestLen) {
                    best = this;
                    bestLen = hrefPath.length;
                }
            }
        });

        if (!best) {
            return;
        }

        var $item = $(best).closest('.menu-item');
        if (!$item.hasClass('menu-item-active') && !$item.hasClass('active')) {
            $item.addClass('active menu-item-active');
        }
    }

    function syncOpenParents() {
        var $menu = $('#layout-menu');
        $menu.find('.menu-item.active, .menu-item.menu-item-active').each(function () {
            $(this).parents('.menu-item-submenu').addClass('open menu-item-open');
        });
    }

    function scrollActiveIntoView() {
        var el = document.querySelector('#layout-menu .menu-item.menu-item-active, #layout-menu .menu-item.active');
        if (el && typeof el.scrollIntoView === 'function') {
            el.scrollIntoView({ block: 'nearest' });
        }
    }

    $(function () {
        var $body = $('body');
        var menu = document.getElementById('layout-menu');

        markActiveFromUrl();
        syncOpenParents();
        scrollActiveIntoView();

        $(document).on('click', '#layout-menu-toggle', function (e) {
            e.preventDefault();
            $body.toggleClass('layout-menu-expanded');
        });

        $(document).on('click', '#layout-menu-close, .layout-overlay', function (e) {
            e.preventDefault();
            $body.removeClass('layout-menu-expanded');
        });

        if (menu) {
            menu.addEventListener('click', function (e) {
                var toggle = e.target.closest('a.menu-toggle');
                if (!toggle) {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
                var item = toggle.closest('li.menu-item');
                if (!item) {
                    return;
                }
                var willOpen = !item.classList.contains('open') && !item.classList.contains('menu-item-open');
                var siblings = item.parentElement ? item.parentElement.children : [];
                Array.prototype.forEach.call(siblings, function (el) {
                    if (el !== item && el.classList.contains('menu-item-submenu')) {
                        el.classList.remove('open', 'menu-item-open');
                    }
                });
                item.classList.toggle('open', willOpen);
                item.classList.toggle('menu-item-open', willOpen);
            }, true);
        }
    });
})(jQuery);
