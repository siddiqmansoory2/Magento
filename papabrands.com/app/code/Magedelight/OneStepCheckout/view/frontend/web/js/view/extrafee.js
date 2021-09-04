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
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magedelight_OneStepCheckout/js/action/osc-loader'
    ],
    function(ko, Component, quote, priceUtils, setShippingInformationAction, Loader) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magedelight_OneStepCheckout/extrafee'
            },
            isExtraFeeEnabled: window.checkoutConfig.mdosc_extrafee_enabled,
            extrafeeCheckboxTitle: window.checkoutConfig.mdosc_extrafee_checkbox_label,
            initObservable: function () {
                var parent = this._super();
                parent.observe({
                    extrafeeChecked: ko.observable(false)
                });

                this.extrafeeChecked.subscribe(function (newValue) {
                    Loader.review(true);
                    setShippingInformationAction().always(
                        function () {
                            Loader.review(false);
                        }
                    );
                });
                return this;
            }
        });
    }
);
