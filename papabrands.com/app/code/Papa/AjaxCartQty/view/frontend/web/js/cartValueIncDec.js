define(['jquery'], function ($) {
    "use strict";
        function ajaxCartUpdate() {
            $('.main').on("click", '.ajax-cart-qty-minus', function () {
                var input = $(this).parent().parent().find('input');
                var value = parseInt(input.val());
                if (value) input.val(value - 1);
            });
            $('.main').on("click", '.ajax-cart-qty-plus', function () {
                var input = $(this).parent().parent().find('input');
                var value = parseInt(input.val());
                input.val(value + 1);
            });
        }

        ajaxCartUpdate();
});