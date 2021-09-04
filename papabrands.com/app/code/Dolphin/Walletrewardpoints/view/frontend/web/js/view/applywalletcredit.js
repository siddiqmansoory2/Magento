define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Dolphin_Walletrewardpoints/js/action/wallet-credit',
    'Dolphin_Walletrewardpoints/js/action/wallet-credit-cancel',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Dolphin_Walletrewardpoints/js/model/payment/credit-messages',
    'Magento_Catalog/js/price-utils',
], function (
    $,
    ko,
    Component,
    quote,
    applyCreditAction,
    applyCreditCancelAction,
    fullScreenLoader,
    getPaymentInformationAction,
    totals,
    messageContainer,
    priceUtils
) {
    'use strict';

    var totals = quote.getTotals(),
        input_credit_val = ko.observable(null),
        is_enable = ko.observable(window.checkoutConfig.is_enable),
        currencySymbol = window.checkoutConfig.currencySymbol,
        customerwalletcredit = ko.observable(parseFloat(window.checkoutConfig.customerwalletcredit)),
        isLoggedIn = ko.observable(window.checkoutConfig.isLoggedIn),
        maximum_allow_credit = ko.observable(window.checkoutConfig.maximum_allow_credit),
        max_allow_credit = ko.observable(window.checkoutConfig.max_allow_credit),
        isApplied,
        allow_with_coupon = window.checkoutConfig.allow_with_coupon,
        applyCreditUrl = window.checkoutConfig.applyCreditUrl;
        var appliedCredit = totals()['total_segments'][1]['value'].toFixed(2);
        if(Math.abs(appliedCredit) > 0) {
            input_credit_val(Math.abs(appliedCredit));
        }
        isApplied = ko.observable(input_credit_val() != null);

    return Component.extend({
        defaults: {
            template: 'Dolphin_Walletrewardpoints/applycreditform'
        },
        input_credit_val: input_credit_val,
        is_enable: is_enable,
        customerwalletcredit: currencySymbol + customerwalletcredit(),
        isLoggedIn: isLoggedIn,
        maximum_allow_credit: maximum_allow_credit,
        isApplied: isApplied,
        allow_with_coupon: allow_with_coupon,

        apply: function () {
            if (max_allow_credit() == 0) {
                messageContainer.addErrorMessage({
                    'message': 'You have no Wallet Credit.'
                });
            }
            if (allow_with_coupon == 0 && totals()['coupon_code'] != null && totals()['discount_amount'] != '0.0000') {
                messageContainer.addErrorMessage({
                        'message': 'Wallet Credit is not apply when coupon is applied.'
                    });
                input_credit_val('');
            } else if (this.validate()) {
                applyCreditAction(applyCreditUrl, input_credit_val(), isApplied, 'credit-apply');
            }
        },

        applyCredit: function () {
            $("#apply_credit_val").attr('max', max_allow_credit());
        },

        cancel: function () {
            if (this.validate()) {
                applyCreditCancelAction(applyCreditUrl, isApplied, 'credit-cancel');
                input_credit_val('');
                isApplied(input_credit_val() != null);
            }
        },

        validate: function () {
            var form = '#credit-wallet-form';

            return $(form).validation() && $(form).validation('isValid');
        }
    });
});
