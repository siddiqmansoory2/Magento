define([
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/form/form',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-billing-address',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/action/set-billing-address',
    'Magedelight_OneStepCheckout/js/action/osc-loader',
    'Magento_Ui/js/model/messageList',
    'mage/translate',
    'Magedelight_OneStepCheckout/js/model/billing-address-state',
    'Magedelight_OneStepCheckout/js/model/google-autocomplete-address'
],
function (
    $,
    ko,
    _,
    Component,
    customer,
    addressList,
    quote,
    createBillingAddress,
    selectBillingAddress,
    checkoutData,
    checkoutDataResolver,
    customerData,
    setBillingAddressAction,
    Loader,
    globalMessageList,
    $t,
    State,
    GoogleAutocompleteAddress
) {
    'use strict';

    var lastSelectedBillingAddress = null,
        newAddressOption = {
            /**
             * Get new address label
             * @returns {String}
             */
            getAddressInline: function () {
                return $t('New Address');
            },
            customerAddressId: null
        },
        countryData = customerData.get('directory-data'),
        addressOptions = addressList().filter(function (address) {
            return address.getType() == 'customer-address';
        });

    addressOptions.push(newAddressOption);

    return Component.extend({
        defaults: {
            template: 'Magedelight_OneStepCheckout/billing-address'
        },
        isVirtual:quote.isVirtual,
        isAddressSameAsShipping: State.sameAsShipping,
        currentBillingAddress: quote.billingAddress,
        addressOptions: addressOptions,
        customerHasAddresses: addressOptions.length > 1,

        /**
         * Init component
         */
        initialize: function () {
            this._super();
            quote.paymentMethod.subscribe(function () {
                checkoutDataResolver.resolveBillingAddress();
            }, this);
        },

        /**
         * @return {exports.initObservable}
         */
        initObservable: function () {
            this._super()
                .observe({
                    selectedAddress: null,
                    isAddressDetailsVisible: quote.billingAddress() != null,
                    isAddressFormVisible: !customer.isLoggedIn() || addressOptions.length === 1,
                    isAddressSameAsShipping: true,
                    saveInAddressBook: 1
                });

            quote.billingAddress.subscribe(function (newAddress) {
              
                if (quote.isVirtual()) {
                    this.isAddressSameAsShipping(false);
                  
                } 

                if (newAddress != null && newAddress.saveInAddressBook !== undefined) {

                    this.saveInAddressBook(newAddress.saveInAddressBook);

                } else {

                    this.saveInAddressBook(1);
                }
                this.isAddressDetailsVisible(true);
            }, this);

            return this;
        },

        canUseShippingAddress: ko.computed(function () {
            return !quote.isVirtual() && quote.shippingAddress() && quote.shippingAddress().canUseForBilling();
        }),


        /**
         * @param {Object} address
         * @return {*}
         */
        addressOptionsText: function (address) {
            return address.getAddressInline();
        },

         checkUseShippingAddress: function (data,event) {
                var useShipping = event.target.checked?true:false;
                State.sameAsShipping(useShipping);
                if(useShipping == false){
                    this.editAddress();
                }
                return true;
            },

        /**
         * @return {Boolean}
         */
        useShippingAddress: function () {
            if (this.isAddressSameAsShipping()) {
                selectBillingAddress(quote.shippingAddress());

                this.updateAddresses();
                this.isAddressDetailsVisible(true);
            } else {
                lastSelectedBillingAddress = quote.billingAddress();
                quote.billingAddress(null);
                this.isAddressDetailsVisible(false);
            }
            checkoutData.setSelectedBillingAddress(null);

            return true;
        },

        /**
         * Update address action
         */
        updateAddress: function () {
            var addressData, newBillingAddress;

            if (this.selectedAddress() && this.selectedAddress() != newAddressOption) { 
               
                selectBillingAddress(this.selectedAddress());
                checkoutData.setSelectedBillingAddress(this.selectedAddress().getKey());
            } else {
                this.source.set('params.invalid', false);
                this.source.trigger(this.dataScopePrefix + '.data.validate');

                if (this.source.get(this.dataScopePrefix + '.custom_attributes')) {
                    this.source.trigger(this.dataScopePrefix + '.custom_attributes.data.validate');
                }

                if (!this.source.get('params.invalid')) {
                    addressData = this.source.get(this.dataScopePrefix);

                    if (customer.isLoggedIn() && !this.customerHasAddresses) { 
                      
                        this.saveInAddressBook(1);
                    }
                    addressData['save_in_address_book'] = this.saveInAddressBook() ? 1 : 0;
                    newBillingAddress = createBillingAddress(addressData);

                  
                    selectBillingAddress(newBillingAddress);
                    checkoutData.setSelectedBillingAddress(newBillingAddress.getKey());
                    checkoutData.setNewCustomerBillingAddress(addressData);
                }
            }
            this.updateAddresses();
        },

        initGoogleSuggestion: function(){
                if(window.checkoutConfig.suggest_address == true && window.checkoutConfig.google_api_key ){
                    setTimeout(function(){
                        GoogleAutocompleteAddress.init('billing-address-form','billing');
                    },2000);
                }
        },

        addCustomClass: function(){
            if (window.checkoutConfig.billing_region_class) {
                setTimeout(function(){
                    if($('.billing-address-form input[name="region"]').length) {
                        $('.billing-address-form input[name="region"]').parent().parent().addClass(window.checkoutConfig.billing_region_class);
                    }
                },1000);
            }
        },

        /**
         * Edit address action
         */
        editAddress: function () {
            lastSelectedBillingAddress = quote.billingAddress();
            quote.billingAddress(null);
            this.isAddressDetailsVisible(false);
            this.addCustomClass();
            this.initGoogleSuggestion();
        },

        /**
         * Cancel address edit action
         */
        cancelAddressEdit: function () {
            this.restoreBillingAddress();

            if (quote.billingAddress()) {
               
                this.isAddressSameAsShipping(
                    quote.billingAddress() != null &&
                        quote.billingAddress().getCacheKey() == quote.shippingAddress().getCacheKey() && 
                        !quote.isVirtual()
                );
                this.isAddressDetailsVisible(true);
            } else {
                State.sameAsShipping(true);
                this.useShippingAddress();
            }
        },

        /**
         * Restore billing address
         */
        restoreBillingAddress: function () {
            if (lastSelectedBillingAddress != null) {
                selectBillingAddress(lastSelectedBillingAddress);
            }
        },

        /**
         * @param {Object} address
         */
        onAddressChange: function (address) {
            this.isAddressFormVisible(address == newAddressOption);
        },

        /**
         * @param {Number} countryId
         * @return {*}
         */
        getCountryName: function (countryId) {
            return countryData()[countryId] != undefined ? countryData()[countryId].name : ''; 
        },

        /**
         * Trigger action to update shipping and billing addresses
         */
        updateAddresses: function () {
            Loader.payment(true);
            Loader.review(true);
            setBillingAddressAction(globalMessageList).always(
                function () {
                    Loader.payment(false);
                    Loader.review(false);
                }
            );
        },

        /**
         * Get code
         * @param {Object} parent
         * @returns {String}
         */
        getCode: function (parent) {
            return _.isFunction(parent.getCode) ? parent.getCode() : 'shared';
        },

        getAfterShippingClass: function () {
            var className = 'md-osc-billing-address';
            if(window.checkoutConfig.displayBillingAfterShippingAddress) {
                className = 'md-osc-billing-address-after-shipping';
            }
            return className;
        }
    });
});
