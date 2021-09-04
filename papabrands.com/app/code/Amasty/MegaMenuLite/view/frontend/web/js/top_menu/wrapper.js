/**
 *  Amasty Top Menu elements UI Component
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'rjsResolver'
], function ($, ko, Component, resolver) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_MegaMenuLite/top_menu/wrapper',
            templates: {
                items: 'Amasty_MegaMenuLite/top_menu/items/wrapper'
            },
            imports: {
                isSticky: "ammenu_wrapper:isSticky",
                isMobile: "ammenu_wrapper:isMobile",
                is_hamburger: "ammenu_wrapper:is_hamburger",
                color_settings: "ammenu_wrapper:color_settings",
                isIconsAvailable: "ammenu_wrapper:isIconsAvailable",
                root_templates: "ammenu_wrapper:templates"
            },
            modules: {
                ammenu: 'index = ammenu_wrapper'
            }
        },

        /**
         * Submenu init method
         */
        initialize: function () {
            var self = this;

            self._super();

            resolver(function () {
                if (self.isMobile || self.is_hamburger) {
                    return false;
                }

                self.ammenu = self.ammenu();
                self.elems(self.ammenu.data.elems);
                self._initElems(self.elems());
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
                    isSticky: false
                });

            return this;
        },

        /**
         * Init Target elements method
         *
         * @params {elems} Object
         */
        _initElems: function (elems) {
            var self = this;

            _.each(elems, function (elem) {
                self._initElem(elem);
            });
        },

        /**
         * Init Target element method
         *
         * @params {elem} Object
         */
        _initElem: function (elem) {
            elem.isIconVisible(!!elem.icon);
        }
    });
});
