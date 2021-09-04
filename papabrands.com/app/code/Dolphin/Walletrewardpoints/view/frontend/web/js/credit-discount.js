define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mage.creditDiscount', {
        options: {
        },

        _create: function () {
            this.creditDiscountValue = $(this.options.creditSelector);
            this.removeCredit = $(this.options.removeCreditSelector);

            $(this.options.applyCredit).on('click', $.proxy(function () {
                this.removeCredit.attr('value', '0');
                $(this.element).validation().submit();
            }, this));

            $(this.options.cancelCredit).on('click', $.proxy(function () {
                this.removeCredit.attr('value', '1');
                this.element.submit();
            }, this));
        }
    });

    return $.mage.creditDiscount;
});
