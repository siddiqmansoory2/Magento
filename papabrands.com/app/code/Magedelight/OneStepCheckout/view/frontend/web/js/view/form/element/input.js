
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
/*browser:true*/
/*global define*/
define([
    'jquery',
    'Magento_Ui/js/form/element/abstract',
    'Magedelight_OneStepCheckout/js/action/validate-shipping-info',
    'Magedelight_OneStepCheckout/js/action/save-shipping-address'
], function ($, abstract,ValidateShippingInfo,SaveAddressBeforePlaceOrder) {
    'use strict';

    return abstract.extend({
        saveShippingAddress: function(){
            if(ValidateShippingInfo()){
                SaveAddressBeforePlaceOrder();
            }
        }
    });
});
