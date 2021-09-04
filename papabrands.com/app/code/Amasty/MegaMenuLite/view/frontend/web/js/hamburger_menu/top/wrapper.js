/**
 *  Amasty Top Menu elements UI Component
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'rjsResolver',
    'underscore'
], function ($, ko, Component, resolver, _) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_MegaMenuLite/top_menu/wrapper',
            templates: {
                items: 'Amasty_MegaMenuLite/top_menu/items/wrapper'
            },
            modules: {
                ammenu: 'index = ammenu_wrapper'
            },
            imports: {
                isMobile: "ammenu_wrapper:isMobile",
                isIconsAvailable: "ammenu_wrapper:isIconsAvailable",
                color_settings: "ammenu_wrapper:color_settings",
                isSticky: "ammenu_wrapper:isSticky",
                is_hamburger: "ammenu_wrapper:is_hamburger",
                root_templates: "ammenu_wrapper:templates",
                isOpen: "hamburger_toggle:isOpen"
            }
        },

        /**
         * Topmenu init method
         */
        initialize: function () {
            var self = this;

            self._super();

            resolver(function () {
                if (self.isMobile || !self.is_hamburger) {
                    return false;
                }

                self.ammenu = self.ammenu();

                self.initElems();
            })
        },

        /**
         * Init observable variables
         *
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe({
                    isSticky: false,
                    isOpen: false
                });

            return this;
        },

        /**
         * Init elements method
         */
        initElems: function () {
            var self = this,
                elems = self.ammenu.data.elems.filter(function (item) {
                    return !item.is_category;
                });

            self.elems(elems);
        }
    });
});
