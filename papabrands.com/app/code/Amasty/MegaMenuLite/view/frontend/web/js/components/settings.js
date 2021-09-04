/**
 *  Amasty Settings UI Component
 */

define([
    'jquery',
    'ko',
    'uiComponent'
], function ($, ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            templates: {
                login: 'Amasty_MegaMenuLite/components/settings/account_links',
                language: 'Amasty_MegaMenuLite/components/settings/language',
                currency: 'Amasty_MegaMenuLite/components/settings/currency',
                dropdown: 'Amasty_MegaMenuLite/components/settings/dropdown'
            },
            imports: {
                color_settings: "ammenu_wrapper:color_settings",
                welcome_message: "ammenu_wrapper:welcome_message",
                settings: "ammenu_wrapper:settings",
                links: "ammenu_wrapper:links",
                root_templates: "ammenu_wrapper:templates"
            },
            listens: {
                'ammenu_wrapper:customer': 'customer',
                'ammenu_wrapper:wishlist': 'wishlist'
            }
        },

        /**
         * Init observable variables
         *
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe({
                    customer: false,
                    welcome_message: false,
                    wishlist: false
                });

            this.welcome_message.backgroundColor = ko.observable(false);

            return this;
        },

        /**
         *  Init target settingItem
         *
         *  @params {Object} settingItem
         */
        initItem: function (settingItem) {
            settingItem.isActive = ko.observable(false);

            settingItem.items = settingItem.items.filter(function (item) {
                return item.code !== settingItem.current_code;
            });
        },

        /**
         *  Init Currency Item
         */
        initCurrency: function () {
            var currency = this.settings.currency;

            this.initItem(currency);
        },

        /**
         *  Init Language Item
         */
        initLanguage: function () {
            var language = this.settings.switcher;

            this.initItem(language);
        }
    });
});
