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

var patch = window.magentoVersion_osc_patch;
var config;
config = {
    map: {
        '*': {
            'Magento_Checkout/js/view/billing-address': 'Magedelight_OneStepCheckout/js/view/billing-address',
            'Magento_Checkout/template/shipping-address/address-renderer/default.html': 'Magedelight_OneStepCheckout/template/shipping-address/address-renderer/default.html'
            // 'Magento_Checkout/js/model/shipping-save-processor/default': 'Magedelight_OneStepCheckout/js/model/shipping-save-processor/default'
        }
    },
    config: {
        'mixins': {
            'Magento_Checkout/js/view/shipping-address/address-renderer/default': {
                'Magedelight_OneStepCheckout/js/view/shipping-address/address-renderer/default-mixins': true
            },
            'Magento_CheckoutAgreements/js/model/agreements-assigner': {
                'Magedelight_OneStepCheckout/js/model/agreements-assigner-mixin': true
            },
            'Magento_Checkout/js/action/place-order': {
                'Magedelight_OneStepCheckout/js/model/place-order-mixin': true
            },
            'Magento_Checkout/js/sidebar': {
                'Magedelight_OneStepCheckout/js/action/sidebar-mixins': true
            },
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender': {
                'Magedelight_OneStepCheckout/js/model/shipping-save-processor/default-mixin': true
            },
            'Magento_Checkout/js/action/set-payment-information': {
                'Magedelight_OneStepCheckout/js/action/set-payment-information-mixin': true
            },
            'Magento_Paypal/js/view/payment/method-renderer/in-context/checkout-express': {
                'Magedelight_OneStepCheckout/js/view/payment/method-renderer/in-context/checkout-express-mixin': true
            }
        }
    }
};
if(patch === '232') {
    config['map']['*']['Magento_Checkout/js/view/billing-address/list'] = 'Magedelight_OneStepCheckout/js/view/billing-address/list';
}