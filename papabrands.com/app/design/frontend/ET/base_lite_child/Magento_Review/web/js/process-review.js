return function (config) {
    // var reviewTab = $(config.reviewsTabSelector),
    //     requiredReviewTabRole = 'tab';

    // if (reviewTab.attr('role') === requiredReviewTabRole && reviewTab.hasClass('active')) {
        processReviews(config.productReviewUrl);
    // } else {
    //     reviewTab.one('beforeOpen', function () {
    //         processReviews(config.productReviewUrl);
    //     });
    // }

    $(function () {
        $('.product-info-main .reviews-actions a').click(function (event) {
            var acnchor;

            event.preventDefault();
            acnchor = $(this).attr('href').replace(/^.*?(#|$)/, '');
            $('.product.data.items [data-role="content"]').each(function (index) { //eslint-disable-line
                if (this.id == 'reviews') { //eslint-disable-line eqeqeq
                    $('.product.data.items').tabs('activate', index);
                    $('html, body').animate({
                        scrollTop: $('#' + acnchor).offset().top - 50
                    }, 300);
                }
            });
        });
    });
};
