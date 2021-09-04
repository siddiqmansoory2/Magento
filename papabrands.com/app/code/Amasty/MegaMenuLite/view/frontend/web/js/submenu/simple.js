/**
 *  Amasty simple submenu UI Component
 */

define([
    'jquery',
    'ko',
    'uiComponent'
], function ($, ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            activeElem: false,
            template: 'Amasty_MegaMenuLite/submenu/simple/wrapper',
            imports: {
                color_settings: "ammenu_wrapper:color_settings",
                isIconsAvailable: "ammenu_wrapper:isIconsAvailable",
                root_templates: "ammenu_wrapper:templates"
            }
        },

        /**
         * Applying Bindings in target element
         */
        applyBindings: function (element) {
            ko.applyBindingsToDescendants(this, element);
        }
    });
});
