/**
 * Advanced toastr defaults for the Sneat shell: titles, close, progress bar.
 */
(function () {
    'use strict';

    if (typeof toastr === 'undefined') {
        return;
    }

    toastr.options = {
        closeButton: true,
        debug: false,
        newestOnTop: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        preventDuplicates: false,
        onclick: null,
        showDuration: 300,
        hideDuration: 300,
        timeOut: 5000,
        extendedTimeOut: 1500,
        showEasing: 'swing',
        hideEasing: 'linear',
        showMethod: 'fadeIn',
        hideMethod: 'fadeOut',
        tapToDismiss: true
    };

    if (toastr._sneatWrapped) {
        return;
    }

    var titles = {
        success: 'Success',
        error: 'Error',
        warning: 'Warning',
        info: 'Info'
    };

    ['success', 'error', 'warning', 'info'].forEach(function (type) {
        var original = toastr[type];
        toastr[type] = function (message, title, optionsOverride) {
            if (title && typeof title === 'object' && !optionsOverride) {
                optionsOverride = title;
                title = titles[type];
            }
            return original.call(toastr, message, title || titles[type], optionsOverride);
        };
    });

    toastr._sneatWrapped = true;
})();
