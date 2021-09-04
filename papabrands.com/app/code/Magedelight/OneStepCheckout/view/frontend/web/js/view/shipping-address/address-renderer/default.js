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
    'Magento_Checkout/js/view/shipping-address/address-renderer/default',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/checkout-data',
    'Magedelight_OneStepCheckout/js/action/reload-shipping-method',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/action/set-billing-address',
    'Magento_Ui/js/model/messageList',
    'Magedelight_OneStepCheckout/js/model/billing-address-state'
], function($, Renderer, formPopUpState,setShippingInformationAction,selectShippingAddressAction,checkoutData,reloadShippingMethod,selectBillingAddress,setBillingAddressAction,globalMessageList,State) {
    'use strict';
    return Renderer.extend({
        isFormPopUpVisible: formPopUpState.isVisible,
        isAddressSameAsShipping: State.sameAsShipping,
        defaults: {
            template: 'Magedelight_OneStepCheckout/shipping-address/address-renderer/default'
        },
        editAddress: function() {
            this.showForm();
        },
        showForm: function() {
            formPopUpState.isVisible(true);
        },
        selectAddress: function () {
            selectShippingAddressAction(this.address());
            checkoutData.setSelectedShippingAddress(this.address().getKey());
            reloadShippingMethod();
            if (this.isAddressSameAsShipping()) {
                selectBillingAddress(this.address());
                setBillingAddressAction(globalMessageList);
            }
            setShippingInformationAction();
        },
    });
});
