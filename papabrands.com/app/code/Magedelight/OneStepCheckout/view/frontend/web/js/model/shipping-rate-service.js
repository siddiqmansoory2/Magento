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
        'uiComponent',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/shipping-rate-processor/new-address',
        'Magento_Checkout/js/model/shipping-rate-processor/customer-address'
    ],
    function($, Component, ko, quote, defaultProcessor, customerAddressProcessor) {
        'use strict';

        return Component.extend({
            processors: [],
            stop: ko.observable(false),
            initialize: function () {
                this._super();
                var self = this;
                self.processors.default =  defaultProcessor;
                self.processors['customer-address'] = customerAddressProcessor;

                quote.shippingAddress.subscribe(function () {
                    if(self.stop() == false){                  
                        var type = quote.shippingAddress().getType();
                        if (self.processors[type]) {
                            self.processors[type].getRates(quote.shippingAddress());
                        } else {
                            self.processors.default.getRates(quote.shippingAddress());
                        }
                    }
                });
            }
        });
    }
);
