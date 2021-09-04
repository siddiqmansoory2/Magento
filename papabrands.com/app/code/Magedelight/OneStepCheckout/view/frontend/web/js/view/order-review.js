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
    'uiComponent',
    "underscore",
    'ko',
    'uiRegistry',
    'Magedelight_OneStepCheckout/js/action/osc-loader'
], function (
    $,
    component,
    _,
    ko,
    registry,
    loader
) {
    'use strict';
    return component.extend({
        initialize: function () {
            loader.review(true);
            this._super();
            loader.review(false);
            return this;
        },
        getSequence: function() {
            return parseInt(registry.get("checkout.steps.order-review").sortOrder) + 1;
        }
    });
});