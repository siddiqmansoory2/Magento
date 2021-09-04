define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'Dolphin_Walletrewardpoints/js/model/payment/credit-messages',
    'mage/storage',
    'mage/translate',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader'
], function (
    ko,
    $,
    quote,
    urlManager,
    errorProcessor,
    messageContainer,
    storage,
    $t,
    getPaymentInformationAction,
    totals,
    fullScreenLoader
) {
    'use strict';
    return function (applyCreditUrl, isApplied, cancel) {
        fullScreenLoader.startLoader();
        return $.post(
            applyCreditUrl,
            {credit_cancel: cancel},
            false
        ).done(function (response) {
            var deferred;
            if (response) {
                deferred = $.Deferred();
                isApplied(false);
                totals.isLoading(true);
                getPaymentInformationAction(deferred);
                $.when(deferred).done(function () {
                    fullScreenLoader.stopLoader();
                    totals.isLoading(false);
                });
                if (response[0].type == "success") {
                    messageContainer.addSuccessMessage({
                        'message': response[0].message
                    });
                } else if(response[0].type == "error") {
                    messageContainer.addErrorMessage({
                        'message': response[0].message
                    });
                }
            }
        }).fail(function (response) {
            fullScreenLoader.stopLoader();
            totals.isLoading(false);
            errorProcessor.process(response, messageContainer);
        });
    };
});