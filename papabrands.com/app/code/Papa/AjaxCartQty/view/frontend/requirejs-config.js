var config = {
    map: {
        '*': {
            'AjaxCart': 'Papa_AjaxCartQty/js/cartValueIncDec',
            'CartQtyUpdate': 'Papa_AjaxCartQty/js/cartQtyUpdate'
        }
    },
    shim: {
        AjaxCart: {
            deps: ['jquery']
        },
        CartQtyUpdate: {
            deps: ['jquery']
        }
    }
};