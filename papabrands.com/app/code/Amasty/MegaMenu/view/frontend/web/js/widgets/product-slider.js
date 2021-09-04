define([
    'jquery',
    'Amasty_Base/vendor/slick/slick.min'
], function ($) {
    $.widget('ammenu.ProductSlider', {
        _create: function () {
            var $slider = $(this.element).slick(this.options),
                observer;

            if ('IntersectionObserver' in window) {
                observer = new IntersectionObserver(this._setSliderPosition.bind(this));
                observer.observe($slider[0]);
            }
        },

        /**
         * Slick Slider Position checking
         *
         * @desc checking and fixing new slick sliders positions
         */
        _setSliderPosition: function () {
            var $slider = $(this.element);

            $slider.slick('slickGoTo', 0);
            $slider.slick('setPosition');
            $slider.slick('setDimensions');
        }
    });

    return $.ammenu.ProductSlider;
});
