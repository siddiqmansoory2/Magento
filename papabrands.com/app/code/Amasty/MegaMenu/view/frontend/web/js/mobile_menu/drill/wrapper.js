/**
 *  Amasty Drill Menu elements UI Component
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
            template: 'Amasty_MegaMenu/mobile_menu/drill/wrapper',
            templates: {
                mainButton: 'Amasty_MegaMenu/mobile_menu/drill/main_button',
                toggleButton: 'Amasty_MegaMenuLite/mobile_menu/items/toggle',
                activeLevel: 'Amasty_MegaMenu/mobile_menu/drill/active_level'
            },
            imports: {
                mobile_class: "ammenu_wrapper:mobile_class",
                color_settings: "ammenu_wrapper:color_settings",
                isMobile: "ammenu_wrapper:isMobile",
                isIconsAvailable: "ammenu_wrapper:isIconsAvailable",
                root_templates: "ammenu_wrapper:templates"
            },
            modules: {
                ammenu: 'index = ammenu_wrapper',
                hamburgerWrapper: 'index = hamburger_wrapper'
            }
        },

        /**
         * Init observable variables
         *
         * @return {Object}
         */
        initObservable: function () {
            var self = this;

            self._super()
                .observe({
                    elems: [],
                    actionAnimation: '-slide-left',
                    activeElem: false
                });

            self.activeElem.subscribe(function (value) {
                self.hamburgerWrapper.isHeaderActive(!value);
                self.hamburgerWrapper.isTitleActive(!value);
            });

            return self;
        },

        /**
         * Drill menu type method
         */
        initialize: function () {
            var self = this;

            self._super();

            resolver(function () {
                if (!self.isMobile || self.mobile_class !== 'drill') {
                    return false;
                }

                self.ammenu = self.ammenu();
                self.hamburgerWrapper = self.hamburgerWrapper();

                self.elems(self.ammenu.data.elems);
                self._initElems(self.elems());
            });
        },

        /**
         * Toggling button method
         *
         * @params {elem} Object
         */
        toggleItem: function (item) {
            this.actionAnimation('-slide-left');
            this.activeElem(item);
        },

        /**
         * Set root category method
         */
        setRootLevel: function () {
            this.actionAnimation('-slide-right');
            this.activeElem(false);
        },

        /**
         * Set previous category method
         */
        setPrevLevel: function () {
            this.actionAnimation('-slide-right');

            if (!this.activeElem().parent.isRoot) {
                this.activeElem(this.activeElem().parent);
            } else {
                this.activeElem(false);
            }
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

                if (elem.elems.length) {
                    self._initElems(elem.elems);
                }
            });
        },

        /**
         * Init Target element method
         *
         * @params {elem} Object
         */
        _initElem: function (elem) {
            elem.isIconVisible(elem.parent.isChildHasIcons);
        }
    })
});
