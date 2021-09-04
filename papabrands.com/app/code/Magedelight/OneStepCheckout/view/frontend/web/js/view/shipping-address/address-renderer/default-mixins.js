define([
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/action/set-billing-address',
    'Magento_Ui/js/model/messageList',
    'Magedelight_OneStepCheckout/js/model/billing-address-state',
    'Magento_Checkout/js/action/set-shipping-information'
], function (selectBillingAddress,setBillingAddressAction,globalMessageList,State,setShippingInformationAction) {
    'use strict';

    return function (Component) {
        return Component.extend({
            isAddressSameAsShipping: State.sameAsShipping,
            selectAddress: function () {
                this._super();
                if (this.isAddressSameAsShipping()) {
                    selectBillingAddress(this.address());
                    setBillingAddressAction(globalMessageList);
                }
                setShippingInformationAction();
            }
        });
    }
});
