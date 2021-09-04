define([
    'Magento_GiftMessage/js/model/url-builder',
    'mage/storage',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/error-processor',
    'mage/url',
    'Magento_Checkout/js/model/quote',
    'underscore',
    'mage/translate',
    'Magedelight_OneStepCheckout/js/action/osc-loader'
], function (urlBuilder, storage, messageList, errorProcessor, url, quote, _, $t, Loader) {
    'use strict';

    return function (giftMessage, remove) {
        var serviceUrl;
        Loader.all(true);
        url.setBaseUrl(giftMessage.getConfigValue('baseUrl'));

        if (giftMessage.getConfigValue('isCustomerLoggedIn')) {
            serviceUrl = urlBuilder.createUrl('/carts/mine/gift-message', {});

            if (giftMessage.itemId !== 'orderLevel') { //eslint-disable-line eqeqeq
                serviceUrl = urlBuilder.createUrl('/carts/mine/gift-message/:itemId', {
                    itemId: giftMessage.itemId
                });
            }
        } else {
            serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/gift-message', {
                cartId: quote.getQuoteId()
            });

            if (giftMessage.itemId !== 'orderLevel') { //eslint-disable-line eqeqeq
                serviceUrl = urlBuilder.createUrl(
                    '/guest-carts/:cartId/gift-message/:itemId',
                    {
                        cartId: quote.getQuoteId(), itemId: giftMessage.itemId
                    }
                );
            }
        }
        messageList.clear();
        storage.post(
            serviceUrl,
            JSON.stringify({
                'gift_message': giftMessage.getSubmitParams(remove)
            })
        ).done(function (response) {
            giftMessage.reset();
            if(remove) {
                messageList.addSuccessMessage({message: $t('Gift messages has been removed')});
            } else {
                messageList.addSuccessMessage({message: $t('Gift messages has been successfully updated')});
            }
            Loader.all(false);
        }).fail(function (response) {
            errorProcessor.process(response);
            Loader.all(false);
        });
    };
});
