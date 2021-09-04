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
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magedelight_OneStepCheckout/js/model/shipping-rate-processor/new-address'
    ],
    function (quote, rateRegistry, defaultProcessor) {
        'use strict';
        return function () {
            var address = quote.shippingAddress();
            rateRegistry.set(address.getCacheKey(),'');
            defaultProcessor.getRates(address);
        };
    }
);
