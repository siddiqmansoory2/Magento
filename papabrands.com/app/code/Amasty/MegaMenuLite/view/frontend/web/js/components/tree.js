/**
 *  Amasty Category Tree UI Component
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
            template: 'Amasty_MegaMenuLite/components/tree/wrapper',
            templates: {
                treeItems: 'Amasty_MegaMenuLite/components/tree/items'
            },
            imports: {
                root_templates: "ammenu_wrapper:templates",
                color_settings: "ammenu_wrapper:color_settings",
                isIconsAvailable: "ammenu_wrapper:isIconsAvailable"
            },
            modules: {
                ammenu: 'index = ammenu_wrapper'
            }
        },

        /**
         * Tree init method
         */
        initialize: function () {
            var self = this;

            self._super();

            resolver(function () {
                self.ammenu = self.ammenu();
            });
        },

        /**
         *  Init Item
         */
        initItem: function () {
            if (this.level() > 1) {
                this.isIconVisible(this.parent.isChildHasIcons);
            } else {
                this.isIconVisible(this.icon);
            }
        }
    });
});
