/**
 *  Amasty Hamburger toggle UI Component
 */

define([
    'jquery',
    'ko',
    'uiComponent'
], function ($, ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_MegaMenuLite/components/buttons/hamburger',
            links: {
                color_settings: "ammenu_wrapper:color_settings",
                is_hamburger: "ammenu_wrapper:is_hamburger",
                isMobile: "ammenu_wrapper:isMobile"
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
                    isOpen: false,
                    is_hamburger: false,
                    isMobile: false,
                    color_settings: false
                });

            return this;
        },

        /**
         * Init toggle button method
         */
        initialize: function () {
            var self = this;

            self._super();
        },

        /**
         *  Toggling open state method
         */
        toggling: function () {
            this.isOpen(!this.isOpen());
        }
    });
});
