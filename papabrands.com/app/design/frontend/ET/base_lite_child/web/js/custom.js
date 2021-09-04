var requireQueue = function(modules, callback) {
    function load(queue, results) {
        if (queue.length) {
            require([queue.shift()], function(result) {
                results.push(result);
                load(queue, results);
            });
        } else {
            callback.apply(null, results);
        }
    }

    load(modules, []);
};
requireQueue([
    'jquery',
    'slick'
], function($) {
    jQuery(document).ready(function() {
        jQuery(".row mx-auto product-slider").slick({
            responsive: [{
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                },
                {
                    breakpoint: 400,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                }
            ]
        });
    });
});