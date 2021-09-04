/* Magedelight
* Copyright (C) 2017 Magedelight <info@magedelight.com>
*
* @category Magedelight
* @package Magedelight_OneStepCheckout
* @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
* @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
* @author Magedelight <info@magedelight.com>
*/
/*global define*/
define(
    [
        'jquery',
        'underscore',
        'Magento_Ui/js/form/form',
        'ko',
        'uiRegistry',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-address/form-popup-state',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'Magento_Catalog/js/price-utils',
        'Magedelight_OneStepCheckout/js/action/osc-loader',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/view/billing-address',
        'Magento_Checkout/js/action/select-billing-address',
        'Magedelight_OneStepCheckout/js/model/billing-address-state',
        'Magento_Checkout/js/model/shipping-rate-service'
    ],
    function (
        $,
        _,
        Component,
        ko,
        registry,
        customer,
        addressList,
        addressConverter,
        quote,
        selectShippingAddress,
        formPopUpState,
        shippingService,
        selectShippingMethodAction,
        setShippingInformationAction,
        stepNavigator,
        checkoutDataResolver,
        checkoutData,
        priceUtils,
        Loader,
        shippingRatesValidator,
        getPaymentInformationAction,
        billingAddress,
        selectBillingAddress,
        State
    ) {
        'use strict';

        var popUp = null;
    
        return Component.extend({
            defaults: {
                template: 'Magedelight_OneStepCheckout/shipping-method/list'
            },
            defaultShippingCarrierCode: ko.observable(window.checkoutConfig.mdosc_default_shipping_carrier_code),
            defaultShippingMethodCode: ko.observable(window.checkoutConfig.mdosc_default_shipping_method_code),
            default_shipping_carrier: ko.observable(false),
            visible: ko.observable(!quote.isVirtual()),
            errorValidationMessage: ko.observable(false),
            rates: shippingService.getShippingRates(),
            isLoading: shippingService.isLoading,
            loading: ko.observable(false),
            isAddressSameAsShipping: State.sameAsShipping,
            testShippingMethod: ko.observable(),
            isSelected: ko.computed(function () {
                if (quote.shippingMethod()) {
                    var a =  quote.shippingMethod() ?
                        quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code
                        : null;
                    return a;
                } else {
                    var allRates = shippingService.getShippingRates()();
                    var defaultMethod = _.where(allRates, {available: true});
                    var carrierCode = window.checkoutConfig.mdosc_default_shipping_carrier_code;
                    var methodCode = window.checkoutConfig.mdosc_default_shipping_method_code;
                    if(allRates.length <= 0){
                        return null;
                    }
                    if(carrierCode && methodCode) {
                        defaultMethod = _.where(allRates, {
                            available: true,
                            carrier_code: carrierCode,
                            method_code: methodCode
                        });
                    }

                    if (defaultMethod.length > 0) {
                        if(allRates.length == 1){
                            $('#s_method_'+defaultMethod[0].method_code).click();
                        } else {
                            $('#s_method_'+defaultMethod[0].carrier_code + '_' + defaultMethod[0].method_code).click();
                        }
                        return defaultMethod[0].carrier_code + '_' + defaultMethod[0].method_code;
                    } else {
                        defaultMethod = _.where(allRates, {
                            available: true
                        });
                        if(allRates.length == 1){
                            $('#s_method_'+defaultMethod[0].method_code).click();
                        } else {
                            $('#s_method_'+defaultMethod[0].carrier_code + '_' + defaultMethod[0].method_code).click();
                        }
                        return defaultMethod[0].carrier_code + '_' + defaultMethod[0].method_code;
                    }
                }
            }),

            /**
             * @return {exports}
             */
            initialize: function () {
                var self = this;
                shippingRatesValidator.validateDelay = 500;
                self._super();

                quote.shippingMethod.subscribe(function () {
                    self.errorValidationMessage(false);
                });

                if(self.isLoading){
                    Loader.shipping(true);
                }else{
                    Loader.shipping(false);
                }

                return this;
            },

            /**
             * @param {Object} shippingMethod
             * @return {Boolean}
             */
            selectShippingMethod: function (shippingMethod) {
                selectShippingMethodAction(shippingMethod);
                checkoutData.setSelectedShippingRate(shippingMethod.carrier_code + '_' + shippingMethod.method_code);
                /* Start Compatible With Store Pickup */
                var shippingAdditional = "checkout.steps.shippingMethods.shippingAdditional";
                var dateBlock = registry.get(shippingAdditional+".additional_block_date");
                var storeBlock = registry.get(shippingAdditional+".additional_block");
                var timeBlock = registry.get(shippingAdditional+".additional_block_date");
                var oscDeliveryBlock = registry.get("checkout.steps.shippingMethods.mdosc-delivery-date.delivery_date");

                if(shippingMethod.method_code === "storepickup") {
                    if(storeBlock.isVisibleStoreContainer() !== '' &&
                        window.checkoutConfig.IsPickupDateEnabel === true
                    ) {
                        dateBlock.canVisibleDateBlock(true);
                        timeBlock.canVisibleTimeBlock(true);
                    }
                    oscDeliveryBlock.isVisibleDelivery(false);
                } else {
                    if(typeof dateBlock !== 'undefined' && dateBlock !== '') {
                        dateBlock.canVisibleDateBlock(false);
                    }
                    if(typeof timeBlock !== 'undefined' && timeBlock !== '') {
                        timeBlock.canVisibleTimeBlock(false);
                    }
                    if(typeof oscDeliveryBlock !== 'undefined' && oscDeliveryBlock !== '') {
                        oscDeliveryBlock.isVisibleDelivery(true);
                    }
                }
                /* End Compatible With Store Pickup */
                return true;
            },

            /**
             * Set shipping information handler
             */
            setShippingInformation: function () {
                var self = this;
               /* if(quote.shippingAddress() && quote.shippingAddress().countryId == 'selected="selected"'){
                    quote.shippingAddress().countryId = window.checkoutConfig.defaultCountryId;
                }
                if(quote.billingAddress() && quote.billingAddress().countryId == 'selected="selected"'){
                    quote.billingAddress().countryId = window.checkoutConfig.defaultCountryId;
                }*/
                self.loading(true);
                Loader.payment(true);
                Loader.review(true);
                Loader.shipping(true);
                setShippingInformationAction().always(
                    function () {
                        Loader.payment(false);
                        Loader.review(false);
                        self.loading(false);
                        Loader.shipping(false);
                    }
                );
                return true;
            },

            /**
             * @return {Boolean}
             */
            validateShippingInformation: function () {
                var shippingAddress,
                    addressData,
                    loginFormSelector = 'form[data-role=email-with-possible-login]',
                    emailValidationResult = customer.isLoggedIn();

                if (!quote.shippingMethod()) {
                    this.errorValidationMessage('Please specify a shipping method.');

                    return false;
                }

                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }
                var shippingRegistry = registry.get("checkout.steps.shipping-step.shippingAddress");
                if (shippingRegistry && shippingRegistry.isFormInline) {
                    shippingRegistry.source.set('params.invalid', false);
                    shippingRegistry.source.trigger('shippingAddress.data.validate');

                    if (shippingRegistry.source.get('shippingAddress.custom_attributes')) {
                        shippingRegistry.source.trigger('shippingAddress.custom_attributes.data.validate');
                    }

                    if (shippingRegistry.source.get('params.invalid') ||
                        !quote.shippingMethod().method_code ||
                        !quote.shippingMethod().carrier_code ||
                        !emailValidationResult
                    ) {
                        return false;
                    }

                    shippingAddress = quote.shippingAddress();
                    addressData = addressConverter.formAddressDataToQuoteAddress(
                        shippingRegistry.source.get('shippingAddress')
                    );


                    for (var field in addressData) {

                        if (addressData.hasOwnProperty(field) &&
                            shippingAddress.hasOwnProperty(field) &&
                            typeof addressData[field] != 'function' &&
                            _.isEqual(shippingAddress[field], addressData[field])
                        ) {
                            shippingAddress[field] = addressData[field];
                        } else if (typeof addressData[field] != 'function' &&
                            !_.isEqual(shippingAddress[field], addressData[field])) {
                            shippingAddress = addressData;
                            break;
                        }
                    }

                    if (customer.isLoggedIn()) {
                        shippingAddress.save_in_address_book = 1;
                    }
                    selectShippingAddress(shippingAddress);

                }

                if (!emailValidationResult) {
                    $(loginFormSelector + ' input[name=username]').focus();

                    return false;
                }

                return true;
            },

            getShippingList: function () {
                var list = [];
                var allCarrier = {};
                var rates = this.rates();
                if(rates && rates.length > 0){
                    ko.utils.arrayForEach(rates, function(method) {
                        if(list.length > 0) {
                            var notfound = true;
                            ko.utils.arrayForEach(list, function(carrier) {
                                if(carrier && carrier.code === method.carrier_code){
                                    carrier.methods.push(method);
                                    notfound = false;
                                }
                            });
                            if(notfound === true){
                                allCarrier = {
                                    code:method.carrier_code,
                                    title:method.carrier_title,
                                    methods:[method]
                                };
                                list.push(allCarrier);
                            }
                        } else {
                            allCarrier = {
                                code:method.carrier_code,
                                title:method.carrier_title,
                                methods:[method]
                            };
                            list.push(allCarrier);
                        }
                    });
                }
                return list;
            },

            isShippingOnList: function(carrier_code,method_code){
                var list = this.getShippingList();
                if(list.length > 0){
                    var carrier = ko.utils.arrayFirst(list, function(carrier) {
                        return (carrier.code === carrier_code);
                    });
                    if(carrier && carrier.methods.length > 0){
                        var method = ko.utils.arrayFirst(carrier.methods, function(method) {
                            return (method.method_code === method_code);
                        });
                        return !!(method);
                    }else{
                        return false;
                    }
                }
                return false;
            },

            getDefaultMethod: function(){
                var self = this;
                var list = this.getShippingList();
                var method = false;
                var availableMethods = [];
                ko.utils.arrayForEach(list, function (data) {
                    if(data.methods.length > 0) {
                        ko.utils.arrayFirst(data.methods, function (m) {
                            if(m.available) {
                                availableMethods.push(m);
                            }
                        });
                    }
                });

                if(availableMethods.length > 0) {
                    method = ko.utils.arrayFirst(availableMethods, function (i) {
                        if( self.defaultShippingCarrierCode &&
                            self.defaultShippingMethodCode
                        ) {
                            if(self.defaultShippingCarrierCode === i.carrier_code &&
                                self.defaultShippingMethodCode === i.method_code
                            ) {
                                return true;
                            }
                        }
                        return true;
                    });
                }
                return method;
            },

            formatPrice: function(amount) {
                amount = parseFloat(amount);
                var priceFormat = window.checkoutConfig.priceFormat;
                return priceUtils.formatPrice(amount, priceFormat)
            },

            selectDefaultMethod: function(){

                if(!checkoutData.getSelectedShippingRate() && this.rates().length > 0){                                   
                    this.selectShippingMethod(this.rates()[0]);
                    this.setShippingInformation();
                }
                return true;
            },

            getSequence: function() {
                return parseInt(registry.get("checkout.steps.shippingMethods").sortOrder) + 1;
            },

            selectAdditionalBlock: function(data,event) {
                event.stopPropagation();
            }

        });
    }
);