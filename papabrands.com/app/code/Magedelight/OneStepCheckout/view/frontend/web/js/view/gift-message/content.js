define([
    'jquery',
    'ko',
    'uiComponent',
    'uiRegistry',
    'Magento_Ui/js/modal/modal',
    'Magento_GiftMessage/js/model/gift-message',
    'Magento_GiftMessage/js/model/gift-options',
    'Magedelight_OneStepCheckout/js/action/gift-options'
], function ($, ko, Component, registry, modal, GiftMessage, giftOptions, giftOptionsService) {
    'use strict';

    var popUp = null;

    return Component.extend({
        defaults: {
            listens: {
                'isPopUpVisible': 'popUpVisibleObserver'
            },
            popUpForm: {
                element: '#mdosc-gift-messages',
                options: {
                    type: 'popup',
                    responsive: 'true',
                    innerScroll: 'true',
                    title: 'Gift Message',
                    modalClass: 'gift_modal',
                    trigger: 'mdosc-gift-messages',
                    buttons: {save: {
                        text: 'Update',
                        class: 'action secondary action-update md-osc-gift-message-edit',
                        attr: {},
                    }, cancel: {
                        text: 'Cancel',
                        class: 'action secondary action-hide-popup',
                    }},
                }
            },
        },

        model: {},
        isPopUpVisible: ko.observable(false),
        /**
         * Component init
         */
        initialize: function () {
            var self = this,
                model;
            self._super();
            this.itemId = this.itemId || 'orderLevel';
            this.itemName = this.itemName || 'orderLevel';
            model = new GiftMessage(this.itemId);
            this.model = model;
            giftOptions.addOption(model);
            this.model.getObservable('isClear').subscribe(function (value) {
                if (value === true) {
                    self.model.getObservable('alreadyAdded')(true);
                }
            });
            this.updateElement();
            this.updateTitle();
            this.updateButton();
        },

        getObservable: function (key) {
            return this.model.getObservable(key);
        },

        submitOptions: function(e) {
            giftOptionsService(this.model);
            /*var popup = modal(this.popUpForm, $('.gift_modal'));
             popup.closeModal();*/
            $('.action.secondary.action-hide-popup').click();
        },

        updateButton: function() {
            var buttons;
            var self = this;
            buttons = this.popUpForm.options.buttons;
            this.popUpForm.options.buttons = [
                {
                    class: buttons.save.class ? buttons.save.class : 'action primary action-save-address',
                    text: buttons.save.text ? buttons.save.text : $t('Update'),
                    click: function () {
                        this.closeModal();
                    }
                },
                {
                    class: buttons.cancel.class ? buttons.cancel.class : 'action secondary action-hide-popup',
                    text: buttons.cancel.text ? buttons.cancel.text : $t('Close'),
                    click: function () {
                        self.getObservable('message')('');
                        this.closeModal();
                    }
                }
            ];
        },

        updateTitle: function() {
            this.popUpForm.options.title = this.itemId !== 'orderLevel' ? 'Gift Message ('+this.itemName+')' : 'Gift Message (Order Level)';
        },

        updateElement: function() {
            this.popUpForm.element = this.itemId !== 'orderLevel' ? '#mdosc-gift-messages-'+this.itemId : '#mdosc-gift-messages-orderlevel';
            this.popUpForm.options.trigger = this.itemId !== 'orderLevel' ? 'mdosc-gift-messages-'+this.itemId : 'mdosc-gift-messages-orderlevel';
        },

        showPopup: function(data, event) {
            data.popUpForm.options.closed = function () {
                data.isPopUpVisible(false);
            };
            var popUpElement = modal(data.popUpForm.options, $(data.popUpForm.element));
            popUpElement.openModal();
            data.isPopUpVisible(true);
            return true;
        },

        /**
         * @return {Boolean}
         */
        isActive: function () {
            return this.model.isGiftMessageAvailable();
        },

        /**
         * @return {Boolean}
         */
        hasActiveOptions: function () {
            var regionData = this.getRegion('additionalOptions'),
                options = regionData(),
                i;

            for (i = 0; i < options.length; i++) {
                if (options[i].isActive()) {
                    return true;
                }
            }

            return false;
        },

        getButtonLabel: function () {
            var label = 'Add Gift Message';
            if (this.model.getObservable('alreadyAdded')()) {
                label = 'Edit Gift Message';
            }
            return label;
        },

        getClassName: function () {
            return this.itemId !== 'orderLevel' ? this.itemId : 'orderlevel';
        },

        removePopup: function (data, event) {
            data.model.getObservable('alreadyAdded')(false);
            data.model.getObservable('message')('');
            data.model.getObservable('recipient')('');
            data.model.getObservable('sender')('');
            giftOptionsService(data.model, true);
        }

    });
});
