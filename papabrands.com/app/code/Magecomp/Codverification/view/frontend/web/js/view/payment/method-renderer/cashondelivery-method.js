define([
    'jquery',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/url',
    'mage/storage',
    'Magento_Checkout/js/model/quote',
    'mage/translate',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/view/payment/default'
], function ($,fullScreenLoader,urlBuilder,storage,quote,$t,messageContainer,Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magecomp_Codverification/payment/cashondelivery'
        },

        isEnable: function () {
            if(window.checkoutConfig.payment.codverification.isenable == 1 &&
                window.checkoutConfig.payment.codverification.isalreadyverify == 0)
            {
                return 'block';
            }
            else
            {
                return 'none';
            }
        },

        getCustomTitle: function () {
            return window.checkoutConfig.payment.codverification.customtitle;
        },

        getNumberInstruction: function () {
            var billing_address = quote.billingAddress();
            return $t("You will Receive OTP On "+billing_address.telephone);
        },

        sendCodcode: function () {
            fullScreenLoader.startLoader();
            var serviceUrl = urlBuilder.build('codverification/sendotp/otp');

            return storage.post(serviceUrl)
                .done(function (response) {
                    if(response.errors)
                    {
                        messageContainer.addErrorMessage({'message': response.message});
                    }
                    else
                    {
                        messageContainer.addSuccessMessage({'message': response.message});
                        $('#codsendotp').hide();
                        $('#codcode').show();
                        $('#codresendotp').show();
                        $('#codverifyotp').show();
                    }
                })
                .fail(function (response) {
                    errorProcessor.process(response,messageContainer);
                })
                .always(function () {
                        fullScreenLoader.stopLoader();
                    }
                );
        },

        resendCodcode: function () {
            fullScreenLoader.startLoader();
            var serviceUrl = urlBuilder.build('codverification/resendotp/otp');

            return storage.post(serviceUrl)
                .done(function (response) {
                    if(response.errors)
                    {
                        messageContainer.addErrorMessage({'message': response.message});
                    }
                    else
                    {
                        messageContainer.addSuccessMessage({'message': response.message});
                        $('#codresendotp').hide();
                    }
                })
                .fail(function (response) {
                    errorProcessor.process(response,messageContainer);
                })
                .always(function () {
                        fullScreenLoader.stopLoader();
                    }
                );
        },

        verifyCodcode: function () {
            $('#codcode').removeClass('mage-error');

            var codcode = $('#codcode').val();
            if(codcode == null || codcode == '')
            {
                $('#codcode').addClass('mage-error');
                messageContainer.addErrorMessage({'message': $t("Please Enter OTP.")});
                return false;
            }

            fullScreenLoader.startLoader();
            var serviceUrl = urlBuilder.build('codverification/verify/otp');

            return storage.post(serviceUrl,JSON.stringify({codcode: codcode}))
                .done(function (response) {
                    if(response.errors)
                    {
                        messageContainer.addErrorMessage({'message': response.message});
                    }
                    else
                    {
                        messageContainer.addSuccessMessage({'message': response.message});
                        $('#codverification').hide();
                    }
                })
                .fail(function (response) {
                    errorProcessor.process(response,messageContainer);
                })
                .always(function () {
                        fullScreenLoader.stopLoader();
                    }
                );
        },

        getInstructions: function () {
            return window.checkoutConfig.payment.instructions[this.item.method];
        }
    });
});
