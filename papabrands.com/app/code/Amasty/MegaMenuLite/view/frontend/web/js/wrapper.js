/**
 *  Amasty MegaMenu Wrapper UI Component
 *
 *  @desc Component Mega Menu Lite Module
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'underscore',
    'Magento_Customer/js/customer-data',
    'rjsResolver'
], function ($, ko, Component, _, customerData, resolver) {
    'use strict';

    return Component.extend({
        defaults: {
            isMobile: $(window).width() <= 1024 ? 1 : 0,
            template: 'Amasty_MegaMenuLite/wrapper',
            templates: {
                header: 'Amasty_MegaMenuLite/components/header',
                link: 'Amasty_MegaMenuLite/components/items/link',
                mobile_link: 'Amasty_MegaMenuLite/mobile_menu/items/link',
                close_button: 'Amasty_MegaMenuLite/components/buttons/close',
                icon: 'Amasty_MegaMenu/items/icon',
                hamburger: 'Amasty_MegaMenuLite/hamburger_menu/top/wrapper',
                toggleButton: 'Amasty_MegaMenuLite/components/buttons/toggle',
                arrowIcon: 'Amasty_MegaMenu/components/icons/arrow'
            }
        },

        /**
         * Init MegaMenu Wrapper
         */
        initialize: function () {
            var self = this;

            self._super();

            self.isIconsAvailable = self.icons_status === 'desktop' && !self.isMobile
                || self.icons_status === 'mobile' && self.isMobile
                || self.icons_status === 'desktopAndMobile';

            self.data.isRoot = true;
            self.isChildHasIcons = self.data.isChildHasIcons;

            self._initElems(self.data.elems, 0, self.data);

            resolver(function () {
                self.customer(customerData.get('customer')());
                self.wishlist(customerData.get('wishlist')());
            });
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
                    wishlist: false,
                    isSticky: false
                });

            return this;
        },

        /**
         * Init Target elements method
         *
         * @params {elems} Object
         * @params {level} number
         * @params {parent} Object
         */
        _initElems: function (elems, level, parent) {
            var self = this;

            _.each(elems, function (elem) {
                self._initElem(elem, level, parent);

                if (elem.elems.length) {
                    self._initElems(elem.elems, level + 1, elem);
                }
            });
        },

        /**
         * Init root submenu element
         *
         * @param {Object} item
         */
        _initRoot: function (item) {
            item.width_value = ko.observable(item.width_value);

            if (item.width === 0) {
                item.width_value('100%');
            }

            if (item.width === 1) {
                item.width_value('auto');
            }

            if (item.width_value() && item.width === 2) {
                item.width_value(item.width_value() + 'px');
            }
        },

        /**
         * Init Target element method
         *
         * @params {elem} Object
         * @params {level} number
         * @params {parent} Object
         */
        _initElem: function (elem, level, parent) {
            elem.isActive = ko.observable(false);
            elem.level = ko.observable(level);
            elem.isContentActive = ko.observable(false);
            elem.isHover = ko.observable(false);
            elem.isVisible = true;

            if (!elem.is_category) {
                this._initCustomItem(elem);
            }

            if (level === 0) {
                this._initRoot(elem);
            }

            if (parent) {
                elem.parent = parent;
                elem.isIconVisible = ko.observable(false);
            }

            this._initElemColors(elem);
        },

        _initCustomItem: function (elem) {
            if (
                elem.status === 2 && this.isMobile ||
                elem.status === 3 && !this.isMobile
            ) {
                elem.isVisible = false;
            }
        },

        /**
         * Init Target element colors method
         *
         * @params {elem} Object
         */
        _initElemColors: function (elem) {
            var self = this;

            elem.color = ko.observable(null);

            elem.isActive.subscribe(function (value) {
                self._setElemColor(elem, value);
            });

            elem.isHover.subscribe(function (value) {
                if (!elem.isActive()) {
                    self._setElemColor(elem, value);
                }
            });

            self._setElemColor(elem);
        },

        /**
         * Set Color in target element method
         *
         * @params {elem} Object
         * @params {value} Object
         */
        _setElemColor: function (elem, value) {
            var self = this,
                nextColor = elem.level() ? self.color_settings.submenu_text : self.color_settings.menu_text;

            if (value) {
                nextColor = self.color_settings.category_hover_color;
            }

            if (!value && elem.current) {
                nextColor = self.color_settings.menu_highlight;
            }

            elem.color(nextColor);
        }
    });
});
