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
        'Magedelight_OneStepCheckout/js/model/shipping-save-processor/default'
    ],
    function(defaultProcessor) {
        'use strict';
        var processors = [];
        processors['default'] =  defaultProcessor;

        return {
            registerProcessor: function(type, processor) {
                processors[type] = processor;
            },
            saveShippingInformation: function (type) {
                var rates = [];
                if (processors[type]) {
                    rates = processors[type].saveShippingInformation();
                } else {
                    rates = processors['default'].saveShippingInformation();
                }
                return rates;
            }
        }
    }
);
