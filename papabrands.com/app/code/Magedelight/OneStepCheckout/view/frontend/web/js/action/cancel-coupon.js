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
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/error-processor',
        'Magento_SalesRule/js/model/payment/discount-messages',
        'mage/storage',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/totals',
        'mage/translate',
        'Magedelight_OneStepCheckout/js/action/reload-shipping-method',
        'Magedelight_OneStepCheckout/js/action/osc-loader'
    ],
    function ($, quote, urlManager, errorProcessor, messageContainer, storage, getPaymentInformationAction, totals, $t, reloadShippingMethod, Loader) {
        'use strict';

        return function (isApplied, isLoading, deferred) {
            Loader.all(true);
            var quoteId = quote.getQuoteId(),
                url = urlManager.getCancelCouponUrl(quoteId),
                message = $t('Your coupon was successfully removed.');
            messageContainer.clear();
            deferred = deferred || $.Deferred();

            return storage.delete(
                url,
                false
            ).done(
                function () {
                    getPaymentInformationAction(deferred);
                    reloadShippingMethod();
                    $.when(deferred).done(function () {
                        isApplied(false);
                        deferred.resolve();
                    });
                }
            ).fail(
                function (response) {
                    totals.isLoading(false);
                    errorProcessor.process(response, messageContainer);
                }
            ).always(
                function () {
                    isLoading(false);
                    Loader.all(false);
                }
            );
        };
    }
);
