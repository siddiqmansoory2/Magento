/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OneStepCheckout
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/error-processor',
        'Magento_SalesRule/js/model/payment/discount-messages',
        'mage/storage',
        'mage/translate',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/totals',
        'Magedelight_OneStepCheckout/js/action/reload-shipping-method',
        'Magedelight_OneStepCheckout/js/action/osc-loader'
    ],
    function (
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
        reloadShippingMethod,
        Loader
    ) {
        'use strict';
        return function (couponCode, isApplied, isLoading, deferred) {
            Loader.all(true);
            var quoteId = quote.getQuoteId();
            var url = urlManager.getApplyCouponUrl(couponCode, quoteId);
            deferred = deferred || $.Deferred();
            return storage.put(
                url,
                {},
                false
            ).done(
                function (response) {
                    if (response) {
                        isLoading(false);
                        isApplied(true);
                        getPaymentInformationAction(deferred);
                        reloadShippingMethod();
                        $.when(deferred).done(function () {
                            deferred.resolve();
                        });                        
                        
                    }
                    Loader.all(false);
                }
            ).fail(
                function (response) {
                    isLoading(false);
                    totals.isLoading(false);
                    errorProcessor.process(response, messageContainer);
                    deferred.reject();
                    Loader.all(false);
                }
            );
        };
    }
);
