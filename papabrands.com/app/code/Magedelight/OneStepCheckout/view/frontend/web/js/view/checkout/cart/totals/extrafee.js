define(
    [
        'ko',
        'Magedelight_OneStepCheckout/js/view/checkout/summary/extrafee',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ], function (ko, Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            totals: quote.getTotals(),

            /**
             * Get formatted price
             * @returns {*|String}
             */
            getValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('mdosc_extra_fee').value;
                }
                return this.getFormattedPrice(price);
            },

            isDisplayed: function () {
                return !!(
                    this.totals() &&
                    totals.getSegment('mdosc_extra_fee').value !== null &&
                    totals.getSegment('mdosc_extra_fee').value !== 0
                );
            }
        });
    }
);
