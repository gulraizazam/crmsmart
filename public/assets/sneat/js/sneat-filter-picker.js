/**
 * Consultancies / Treatments list: show 2–3 filters by default.
 * Extra fields stay in the DOM (same IDs) so datatable/export JS is unchanged.
 */
(function ($) {
    'use strict';

    var HIDDEN = 'sneat-filter-item-hidden';

    function itemHasValue($item) {
        var found = false;
        $item.find('select, input').each(function () {
            var val = $(this).val();
            if (val !== null && val !== undefined && String(val).trim() !== '') {
                found = true;
                return false;
            }
        });
        return found;
    }

    function refreshSelect2($item) {
        $item.find('select').each(function () {
            var $select = $(this);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.next('.select2-container').css('width', '100%');
            }
        });
    }

    function syncMenu($bar) {
        $bar.find('.js-filter-menu-item').each(function () {
            var key = $(this).data('filter');
            var $item = $bar.find('.js-filter-item[data-filter="' + key + '"]');
            $(this).toggleClass(HIDDEN, !$item.hasClass(HIDDEN));
        });
        var remaining = $bar.find('.js-filter-menu-item').not('.' + HIDDEN).length;
        $bar.find('.js-add-filter-wrap').toggleClass(HIDDEN, remaining === 0);
    }

    function showItem($item) {
        $item.removeClass(HIDDEN);
        refreshSelect2($item);
        syncMenu($item.closest('.js-filter-bar'));
    }

    function hideItem($item, clear) {
        var $bar = $item.closest('.js-filter-bar');
        if (clear) {
            $item.data('sneat-removing', true);
            $item.find('select').each(function () {
                $(this).val(null).trigger('change');
            });
            $item.find('input').val('').trigger('change');
            $item.removeData('sneat-removing');
        }
        $item.addClass(HIDDEN);
        syncMenu($bar);
    }

    function syncFromValues($bar) {
        $bar.find('.js-filter-item.is-filter-optional').each(function () {
            var $item = $(this);
            if (itemHasValue($item)) {
                showItem($item);
            }
        });
        syncMenu($bar);
    }

    function closeMenus() {
        $('.js-filter-menu').addClass(HIDDEN);
        $('.js-add-filter-btn').attr('aria-expanded', 'false');
    }

    function initBar($bar) {
        if ($bar.data('sneat-filter-ready')) {
            return;
        }
        $bar.data('sneat-filter-ready', true);

        $bar.find('.js-filter-item.is-filter-optional').each(function () {
            var $item = $(this);
            if (!itemHasValue($item)) {
                $item.addClass(HIDDEN);
            }
        });
        syncMenu($bar);

        $bar.on('click', '.js-add-filter-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $menu = $bar.find('.js-filter-menu');
            var open = $menu.hasClass(HIDDEN);
            closeMenus();
            if (open) {
                $menu.removeClass(HIDDEN);
                $(this).attr('aria-expanded', 'true');
            }
        });

        $bar.on('click', '.js-filter-menu-item', function (e) {
            e.preventDefault();
            var key = $(this).data('filter');
            var $item = $bar.find('.js-filter-item[data-filter="' + key + '"]');
            showItem($item);
            closeMenus();
            $item.find('select, input').filter(':visible').first().focus();
        });

        $bar.on('click', '.js-remove-filter', function (e) {
            e.preventDefault();
            hideItem($(this).closest('.js-filter-item'), true);
        });

        $bar.on('change', '.js-filter-item.is-filter-optional :input', function () {
            var $item = $(this).closest('.js-filter-item');
            if ($item.data('sneat-removing')) {
                return;
            }
            if (itemHasValue($item)) {
                showItem($item);
            }
        });

        $bar.on('click', '.js-filter-menu', function (e) {
            e.stopPropagation();
        });
    }

    window.SneatFilterPicker = {
        init: function () {
            $('.js-filter-bar').each(function () {
                initBar($(this));
            });
        },
        syncAll: function () {
            $('.js-filter-bar').each(function () {
                syncFromValues($(this));
            });
        },
        resetOptional: function ($bar) {
            $bar = $bar && $bar.length ? $bar : $('.js-filter-bar');
            $bar.find('.js-filter-item.is-filter-optional').each(function () {
                hideItem($(this), false);
            });
            closeMenus();
        }
    };

    if (typeof window.setFilters === 'function') {
        var originalSetFilters = window.setFilters;
        window.setFilters = function (filterValues, activeFilters) {
            originalSetFilters.apply(this, arguments);
            window.SneatFilterPicker.syncAll();
        };
    }

    $(function () {
        window.SneatFilterPicker.init();

        $(document).on('click', function () {
            closeMenus();
        });

        $(document).on('click', '.js-filter-bar #reset-filters', function () {
            var $bar = $(this).closest('.js-filter-bar');
            setTimeout(function () {
                window.SneatFilterPicker.resetOptional($bar);
            }, 0);
        });
    });
})(jQuery);
