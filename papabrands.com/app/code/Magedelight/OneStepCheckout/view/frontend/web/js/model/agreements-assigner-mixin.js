define([
    'mage/utils/wrapper',
    'jquery'
], function (wrapper, $) {
    'use strict';

    return function (agreementsAssignerAction) {
        return wrapper.wrap(agreementsAssignerAction, function (originalAction, paymentData) {
            originalAction(paymentData);
            if(window.checkoutConfig.agreement_block === 'sidebar') {
                var agreementForm,
                    agreementData,
                    agreementIds;

                agreementForm = $('div[data-role=checkout-agreements] input');
                agreementData = agreementForm.serializeArray();
                agreementIds = [];

                agreementData.forEach(function (item) {
                    agreementIds.push(item.value);
                });

                if (paymentData['extension_attributes'] === undefined) {
                    paymentData['extension_attributes'] = {};
                }

                paymentData['extension_attributes']['agreement_ids'] = agreementIds;
                return paymentData;
            }
        });
    };
});