/*
 * *
 *  Copyright © 2016 Magedelight. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
define([
    'Magento_Ui/js/form/components/group'
], function (Group) {
    'use strict';

    return Group.extend({
        defaults: {
            visible: true,
            label: '',
            showLabel: true,
            required: false,
            template: 'ui/group/group',
            fieldTemplate: 'Magedelight_OneStepCheckout/form/field',
            breakLine: true,
            validateWholeGroup: false,
            additionalClasses: {}
        },

        /**
         * Extends this with defaults and config.
         * Then calls initObservable, iniListenes and extractData methods.
         */
        initialize: function () {
            this._super();
            return this;
        }
    });
});
