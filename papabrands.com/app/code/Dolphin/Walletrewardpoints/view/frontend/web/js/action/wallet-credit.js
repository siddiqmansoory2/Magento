define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/error-processor',
    'Dolphin_Walletrewardpoints/js/model/payment/credit-messages',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader'
], function (
    ko,
    $,
    errorProcessor,
    messageContainer,
    getPaymentInformationAction,
    totals,
    fullScreenLoader
) {
    'use strict';

    return function (applyCreditUrl, inputCreditVal, isApplied, credit) {
        fullScreenLoader.startLoader();
        return $.post(
            applyCreditUrl,
            {inputCreditVal:inputCreditVal, credit_apply:credit},
            true
        ).done(function (response) {
            var deferred;
            if (response) {
                deferred = $.Deferred();
                getPaymentInformationAction(deferred);
                if (response[0].type == "success") {
                    messageContainer.addSuccessMessage({
                        'message': response[0].message
                    });
                    isApplied(true);
                } else if (response[0].type == "error") {
                    messageContainer.addErrorMessage({
                        'message': response[0].message
                    });
                }
                fullScreenLoader.stopLoader();
            }
        }).fail(function (response) {
            fullScreenLoader.stopLoader();
            totals.isLoading(false);
            errorProcessor.process(response, messageContainer);
        });
    };
});
