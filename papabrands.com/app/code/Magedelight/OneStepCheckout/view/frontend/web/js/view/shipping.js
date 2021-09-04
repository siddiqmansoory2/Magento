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
        'underscore',
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-address/form-popup-state',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/full-screen-loader',
        'uiRegistry',
        'mage/translate',
        'Magedelight_OneStepCheckout/js/model/shipping-rate-service',
        'Magedelight_OneStepCheckout/js/model/validate-shipping',
        'Magedelight_OneStepCheckout/js/model/google-autocomplete-address',
        'Magento_Checkout/js/action/set-billing-address',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Ui/js/model/messageList',
        'Magedelight_OneStepCheckout/js/model/billing-address-state',
        'Amazon_Payment/js/model/storage'
    ],
    function (
        $,
        _,
        Component,
        ko,
        addressList,
        quote,
        shippingService,
        createShippingAddress,
        selectShippingAddress,
        setShippingInformationAction,
        shippingRatesValidator,
        formPopUpState,
        modal,
        checkoutDataResolver,
        checkoutData,
        fullScreenLoader,
        registry,
        $t,
        shippingRateService,
        ValidateShipping,
        GoogleAutocompleteAddress,
        setBillingAddressAction,
        selectBillingAddress,
        globalMessageList,
        State,
        amazonStorage
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magedelight_OneStepCheckout/shipping'
            },
            visible: ko.observable(!quote.isVirtual()),
            isFormPopUpVisible: formPopUpState.isVisible,
            isFormInline: addressList().length == 0,
            isNewAddressAdded: ko.observable(false),
            saveInAddressBook: 1,
            quoteIsVirtual: quote.isVirtual(),
            isAddressSameAsShipping: State.sameAsShipping,
            shipping:ko.observable(false),
            /**
             * @return {exports}
             */
            initialize: function () {

                var self = this,
                    hasNewAddress,
                    fieldsetName = 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset';

                self.shipping.subscribe(function(){
                    if(self.shipping() == true){
                        fullScreenLoader.startLoader();
                    }
                    if(self.shipping() == false){
                        fullScreenLoader.stopLoader();
                    }
                });
                //quote.shippingAddress.subscribe(setShippingInformationAction());

                shippingService.isLoading.subscribe(function(){
                    if(shippingService.isLoading() == true){
                        self.shipping(true);
                    }
                    if(shippingService.isLoading() == false){
                        self.shipping(false);
                    }
                });

                self._super();

                shippingRatesValidator.initFields(fieldsetName);

                checkoutDataResolver.resolveShippingAddress();

                hasNewAddress = addressList.some(function (address) {
                    return address.getType() == 'new-customer-address';
                });

                this.isNewAddressAdded(hasNewAddress);

                // Resolved Issue of Amazon pay
                amazonStorage.isAmazonAccountLoggedIn.subscribe(function (value) {
                    this.isNewAddressAdded(value);
                }, this);

                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var shippingAddressData = checkoutData.getShippingAddressFromData();
                    if (shippingAddressData) {
                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend({}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                        );
                    }
                    checkoutProvider.on('shippingAddress', function (shippingAddressData) {
                        checkoutData.setShippingAddressFromData(shippingAddressData);
                    });
                    self.source = checkoutProvider;
                });
                return this;
            },

            /**
             * Display address form
             */
            showFormAddress: function () {
                this.isFormPopUpVisible(true);
            },

            /**
             * Save new shipping address
             */
            saveNewAddress: function () {
                var addressData,
                    newShippingAddress;
                this.source.set('params.invalid', false);
                this.source.trigger('shippingAddress.data.validate');

                if (!this.source.get('params.invalid')) {
                    addressData = this.source.get('shippingAddress');
                    /** if user clicked the checkbox, its value is true or false. Need to convert.*/
                    addressData.save_in_address_book = this.saveInAddressBook ? 1 : 0;

                    /** New address must be selected as a shipping address */
                    newShippingAddress = createShippingAddress(addressData);
                    selectShippingAddress(newShippingAddress);

                    checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                    checkoutData.setSelectedBillingAddress(newShippingAddress.getKey());
                    checkoutData.setNewCustomerShippingAddress(addressData);

                    this.hideAddressForm();
                    this.isNewAddressAdded(true);

                    if (this.isAddressSameAsShipping()) {
                        selectBillingAddress(newShippingAddress);
                        setBillingAddressAction(globalMessageList);
                    }
                    // setShippingInformationAction();
                }
            },

            /**
             * cancel new shipping address
             */
            hideAddressForm: function () {
                this.isFormPopUpVisible(false);
            },

            /**
             * sort the address field base on sortOrder
             * @param {UIclass} fields
             * @returns {Boolean}
             */
            sortFields: function(fields){
                if(fields.elems().length > 0){
                    var allFields = fields.elems();

                    var regionId, region;
                    $.each(allFields, function (index, value) {

                        if (value.inputName == 'region_id') {
                            regionId = value;
                        }
                        if (value.inputName == 'region') {
                            region = value;
                        }
                    });


                    if(regionId && region){
                        region.sortOrder = regionId.sortOrder;
                    }

                    fields.elems().sort(function(fieldOne, fieldTwo){
                        return parseFloat(fieldOne.sortOrder) > parseFloat(fieldTwo.sortOrder) ? 1 : -1
                    });
                }
                return true;
            },
            initElement: function(element) {
                if (element.index === 'shipping-address-fieldset') {
                    shippingRatesValidator.bindChangeHandlers(element.elems(), false);
                }
            },


            initGoogleSuggestion: function(){
                if(window.checkoutConfig.suggest_address && window.checkoutConfig.google_api_key ){
                    setTimeout(function(){
                        GoogleAutocompleteAddress.init('co-shipping-form','shipping');
                    },2000);
                }
            },

            getSequence: function() {
                return parseInt(registry.get("checkout.steps.shipping-step").sortOrder) + 1;
            }
        });
    }
);
