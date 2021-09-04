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
        'mage/storage',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/quote',
        'Magedelight_OneStepCheckout/js/action/reload-shipping-method',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Ui/js/modal/confirm',
        'Magento_Ui/js/modal/alert',
        'mage/translate',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (
        $,
        Component,
        storage,
        customerData,
        getTotalsAction,
        totals,
        quote,
        reloadShippingMethod,
        getPaymentInformation,
        confirm,
        alertPopup,
        Translate,
        priceUtils,
        fullScreenLoader
    ) {
        "use strict";
        return Component.extend({

            params: '',

            defaults: {
                template: 'Magedelight_OneStepCheckout/summary/item/details'
            },

            isEditAllowed: function() {
                return window.checkoutConfig.edit_product;
            },

            getValue: function(quoteItem) {
                console.log(quoteItem);
                return quoteItem.name;
            },

            addQty: function (data) {
                this.update(data.item_id, 'update', data.qty + 1);
            },

            minusQty: function (data) {
                this.update(data.item_id, 'update', data.qty - 1);
            },

            updateNewQty: function (data) {
                this.update(data.item_id, 'update', data.qty);
            },

            getEditUrl: function(quoteItem) {
                var allItems = customerData.get('cart')().items;
                var product =  _.findWhere(allItems, {'item_id': quoteItem.item_id.toString()});
                if(product) {
                    return product.configure_url ? product.configure_url : '#';
                }
                return '#';
            },
            
            deleteItem: function (data) {
                var self = this;
                confirm({
                    content: Translate('Do you want to remove the item from cart?'),
                    actions: {
                        confirm: function () {
                            self.update(data.item_id, 'delete', '');
                        },
                        always: function (event) {
                            event.stopImmediatePropagation();
                        }
                    }
                });

            },

            editItem: function (data) {
                console.log(data);
                var self = this;
                var url = self.getEditUrl(data);
                confirm({
                    content: Translate('Do you want to redirect on the product page?'),
                    actions: {
                        confirm: function () {
                            window.location = url;
                        },
                        always: function (event) {
                            event.stopImmediatePropagation();
                        }
                    }
                });

            },

            showOverlay: function () {
                fullScreenLoader.startLoader();
//                $('#ajax-loader3').show();
//                $('#review_overlay').show();
            },

            hideOverlay: function () {
                fullScreenLoader.stopLoader();
//                $('#ajax-loader3').hide();
//                $('#review_overlay').hide();
            },

            showPaymentOverlay: function () {
                $('#control_overlay_payment').show();
                $('#ajax-payment').show();
            },

            hidePaymentOverlay: function () {
                $('#control_overlay_payment').hide();
                $('#ajax-payment').hide();
            },

            update: function (itemId, type, qty) {
                var params = {
                    itemId: itemId,
                    qty: qty,
                    updateType: type
                };
                var self = this;
                this.showOverlay();
                storage.post(
                    'onestepcheckout/cart/update',
                    JSON.stringify(params),
                    false
                ).done(
                    function (result) {
                        var miniCart = $('[data-block="minicart"]');
                        miniCart.trigger('contentLoading');
                        customerData.reload('cart', true);
                        miniCart.trigger('contentUpdated');
                    }
                ).fail(
                    function (result) {
                        console.log(result);
                    }
                ).always(
                    function (result) {
                        if (result.error) {
                            alertPopup({
                                content: Translate(result.error),
                                autoOpen: true,
                                clickableOverlay: true,
                                focus: "",
                                actions: {
                                    always: function(){

                                    }
                                }
                            });
                        }

                        if(result.cartEmpty || result.is_virtual){
                            window.location.reload();
                        }
                        
                        reloadShippingMethod();
                        getPaymentInformation().done(function () {
                            self.hideOverlay();
                        });

                    }
                );
            }
        });
    }
);
