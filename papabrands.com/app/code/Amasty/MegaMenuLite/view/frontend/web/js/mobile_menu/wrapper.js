/**
 *  Amasty Mobile Menu UI Component
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'rjsResolver',
    'underscore',
    'mage/translate'
], function ($, ko, Component, resolver, _) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_MegaMenuLite/mobile_menu/wrapper',
            templates: {
                accordion: 'Amasty_MegaMenuLite/mobile_menu/accordion/wrapper',
                tabsSwitcher: 'Amasty_MegaMenuLite/mobile_menu/tabs_switcher',
                typeSwitcher: 'Amasty_MegaMenuLite/mobile_menu/type_switcher',
                itemsAccordion: 'Amasty_MegaMenuLite/mobile_menu/accordion/items/wrapper',
                toggleButton: 'Amasty_MegaMenuLite/mobile_menu/items/toggle'
            },
            modules: {
                ammenu: 'index = ammenu_wrapper'
            },
            imports: {
                root_templates: "ammenu_wrapper:templates",
                color_settings: "ammenu_wrapper:color_settings",
                isChildHasIcons: "ammenu_wrapper:isChildHasIcons",
                settings: "ammenu_wrapper:settings",
                isMobile: "ammenu_wrapper:isMobile",
                isIconsAvailable: "ammenu_wrapper:isIconsAvailable",
                mobileClass: "ammenu_wrapper:mobile_class",
                isOpen: "hamburger_toggle:isOpen"
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
                    tabsList: [
                        {title: $.mage.__('Menu')},
                        {title: $.mage.__('Account')}
                    ],
                    elems: [],
                    isOpen: false,
                    mobileClass: '',
                    activeTab: 0
                });

            return this;
        },

        /**
         *  Mobile menu init method
         */
        initialize: function () {
            var self = this;

            self._super();

            resolver(function () {
                if (!self.isMobile) {
                    return false;
                }

                self.ammenu = self.ammenu();
                self.elems(self.ammenu.data.elems);
                self._initElems(self.elems());
                self._initTabList();
            })
        },

        /**
         * Toggling button method
         *
         * @params {elem} Object
         */
        toggleItem: function (item) {
            item.isActive(!item.isActive());
        },

        /**
         * Init Target elements method
         *
         * @params {elems} Object
         */
        _initElems: function (elems) {
            var self = this;

            _.each(elems, function (elem) {
                if (elem.elems.length) {
                    self._initElems(elem.elems);
                    self._initAllLink(elem);
                }

                self._initElem(elem);
            });
        },

        /*
         * Init Target element method
         *
         * @params {elem} Object
         */
        _initElem: function (elem) {
            elem.isIconVisible(elem.parent.isChildHasIcons);
        },

        /*
         * Init 'ALL of Link' for target element items list method
         *
         * @params {elem} Object
         */
        _initAllLink: function (elem) {
            elem.elems.unshift({
                name: $.mage.__('All') + ' ' + elem.name,
                url: elem.url,
                elems: [],
                color: ko.observable(elem.elems[0].color()),
                isChildHasIcons: ko.observable(false),
                isIconVisible: ko.observable(false),
                level: ko.observable(elem.level()),
                parent: elem
            });
        },

        /**
         * Init Tabs switcher List method
         */
        _initTabList: function () {
            if (this.settings.currency.items.length > 1 || this.settings.switcher.items.length > 1) {
                this.tabsList.push({title: $.mage.__('Settings')});
            }
        },
    });
});
