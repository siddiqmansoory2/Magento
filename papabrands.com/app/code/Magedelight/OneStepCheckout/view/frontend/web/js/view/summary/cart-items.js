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
        'Magento_Checkout/js/model/totals',
        'uiComponent',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/quote',
    ],
    function ($, ko, totals, Component, stepNavigator, quote) {
        'use strict';
        return Component.extend({
            initialize: function () {
                this._super();
                var self = this;
                totals.isLoading.subscribe(function () {
                    if (totals.isLoading() == true) {
                        self.showOverlay();
                    } else {
                        self.hideOverlay();
                    }
                });
            },
            defaults: {
                template: 'Magedelight_OneStepCheckout/summary/cart-items'
            },
            totals: totals.totals(),
            getItems: totals.getItems(),
            getItemsQty: function() {
                return parseFloat(this.totals.items_qty);
            },

            showOverlay: function () {
                $('#ajax-loader3').show();
                $('#control_overlay_review').show();
            },

            hideOverlay: function () {
                $('#ajax-loader3').hide();
                $('#control_overlay_review').hide();
            },

            isItemsBlockExpanded: function () {
                return quote.isVirtual() || stepNavigator.isProcessed('shipping');
            }

        });
    }
);
