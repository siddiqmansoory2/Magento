/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OneStepCheckout
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
define(
    [
        'ko',
        'Magento_Checkout/js/view/summary/item/details/thumbnail'
    ],
    function (ko, Component) {
        return Component.extend({
            defaults: {
                template: 'Magedelight_OneStepCheckout/summary/item/details/thumbnail'
            },
            isShowImage: true
        });
    }
);