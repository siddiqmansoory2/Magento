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
define([
    'jquery',
    'mage/utils/wrapper',
    'Magedelight_OneStepCheckout/js/action/osc-loader'
], function (
    $,
    wrapper,
    loader
) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            loader.all(true);
            return originalAction(paymentData, messageContainer).always(
                function () {
                    loader.all(false);
                }
            );
        });
    };
});
