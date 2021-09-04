define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.sidebar', widget, {
            _removeItemAfter: function (elem) {
                this._super(elem);
                if (window.location.href.split('#')[0] === window.checkout.checkoutUrl) {
                    window.location.reload(false);
                }
            },
            _updateItemQtyAfter: function (elem) {
                this._super(elem);
                if (window.location.href.split('#')[0] === window.checkout.checkoutUrl) {
                    window.location.reload(false);
                }
            }
        });
        return $.mage.sidebar;
    }
});
