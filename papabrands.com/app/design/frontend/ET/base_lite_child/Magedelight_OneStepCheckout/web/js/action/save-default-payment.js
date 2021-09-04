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
        'ko',
        'underscore',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/method-converter'
    ],
    function(
        $,
        ko,
        _,
        paymentService,
        quote,
        selectPaymentMethodAction,
        checkoutData,
        methodConverter
    ) {
        'use strict';

        paymentService.setPaymentMethods(methodConverter(window.checkoutConfig.paymentMethods));
        return function() {
            if (paymentService.getAvailablePaymentMethods().length > 0) {
                var methods = paymentService.getAvailablePaymentMethods();
                var firstMethod = _.first(methods);
                var defaultmethod = window.checkoutConfig.mdosc_default_payment_method;
                var selectedMethod = '';
                if (!_.isUndefined(defaultmethod) || !_.isNull(defaultmethod)) {
                    selectedMethod = _.findWhere(methods, { method: defaultmethod });
                }
                if ((_.isUndefined(selectedMethod) || _.isNull(selectedMethod)) &&
                    (!_.isUndefined(firstMethod) || !_.isNull(firstMethod))
                ) {
                    selectedMethod = firstMethod;
                }
                if (selectedMethod && !quote.paymentMethod()) {
                    selectPaymentMethodAction(selectedMethod);
                    checkoutData.setSelectedPaymentMethod(selectedMethod.method);
                }
            }
        };
    }
);