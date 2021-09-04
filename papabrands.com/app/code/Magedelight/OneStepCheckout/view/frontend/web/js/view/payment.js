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
        "underscore",
        'ko',
        'uiRegistry',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'mage/translate',
        'Magento_Checkout/js/view/payment',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/method-list',
        'Magento_Checkout/js/action/get-payment-information',
        'Magedelight_OneStepCheckout/js/action/save-default-payment',
        'Magedelight_OneStepCheckout/js/action/osc-loader'
    ],
    function (
        $,
        _,
        ko,
        registry,
        paymentService,
        methodConverter,
        $t,
        Payment,
        quote,
        methodList,
        getPaymentInformation,
        saveDefaultPayment,
        loader
    ) {
        'use strict';

        /** Set payment methods to collection */
        paymentService.setPaymentMethods(methodConverter(window.checkoutConfig.paymentMethods));

        return Payment.extend({
            defaults: {
                template: 'Magedelight_OneStepCheckout/payment',
                activeMethod: ''
            },
            isVisible: ko.observable(quote.isVirtual()),
            quoteIsVirtual: quote.isVirtual(),
            initialize: function () {
                loader.payment(true);
                this._super();
                this.navigate();
                methodList.subscribe(function () {
                    saveDefaultPayment();
                });
                loader.payment(false);
                return this;
            },

            /**
             * Navigate method.
             */
            navigate: function () {
                var self = this;
                loader.payment(true);
                getPaymentInformation().done(function () {
                    self.isVisible(true);
                    loader.payment(true);
                }).fail(function () {
                    loader.payment(false);
                });
            },
            getSequence: function() {
                return parseInt(registry.get("checkout.steps.billing-step").sortOrder) + 1;
            }
        });
    }
);
