/**
 *  Amasty Menu Overlay UI Component
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
            template: 'Amasty_MegaMenuLite/components/overlay',
            modules: {
                hamburgerToggle: 'index = hamburger_toggle'
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
                    'isVisible': false
                });

            return this;
        },

        /**
         * Overlay init method
         */
        initialize: function () {
            var self = this;

            self._super();

            resolver(function () {
                self.hamburgerToggle = self.hamburgerToggle();

                self.hamburgerToggle.isOpen.subscribe(function (value) {
                    self.isVisible(value);
                });
            });
        },

        /**
         * Hamburger button toggling method
         */
        toggling: function () {
            this.hamburgerToggle.isOpen(!this.hamburgerToggle.isOpen())
        }
    });
});
