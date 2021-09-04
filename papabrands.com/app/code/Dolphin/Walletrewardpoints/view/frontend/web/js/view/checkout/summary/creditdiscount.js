define([
    'jquery',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals',
    'Dolphin_Walletrewardpoints/js/view/applywalletcredit'
], function ($, Component, quote, priceUtils, totals, applywalletcredit) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Dolphin_Walletrewardpoints/checkout/summary/creditdiscount'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function isDisplayed() {
                return this.isFullMode();
            },
            getValue: function () {
                var price = 0;
                if (this.totals()) {
                    if(totals.getSegment('creditdiscount')){
                       price = totals.getSegment('creditdiscount').value;
                       $("#apply_credit_val").val(Math.abs(price));
                    }
                }
                return this.getFormattedPrice(price);
            },
            getValuewithoutformatted: function () {
                var price = 0;
                if (this.totals()) {
                    if(totals.getSegment('creditdiscount')){
                       price = totals.getSegment('creditdiscount').value;
                    }
                }
                if (price == 0.00) {
                    applywalletcredit().isApplied(false);
                    $('#apply_credit_val').removeAttr("disabled").val('');
                }
                return price;
            },
            getBaseValue: function () {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().value;
                }
                return priceUtils.formatPrice(price, quote.getBasePriceFormat());
            }
        });
    }
);
