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
        'uiRegistry',
        'Magedelight_OneStepCheckout/js/view/onestepcheckout'
    ],
    function (
        $,
        Component,
        ko,
        registry,
        onestepcheckout
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magedelight_OneStepCheckout/place-order',
            },
            isBtnVisible: ko.observable(true),
            isCheckoutEnable: ko.pureComputed(function(){
                return onestepcheckout().isCheckoutEnable();
            }),
            initialize: function () {
                this._super();
            },

            prepareToPlaceOrder: function() {
                onestepcheckout().startPlaceOrder();
            }
        });
    }
);
