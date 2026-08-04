// assets/js/layout_news_slider.js

jQuery(function($) {

    var $el = $('.news__slides');

    if ($el.length && !$el.hasClass('slick-initialized')) {

        $el.slick({
            slidesToShow: 3,
            slidesToScroll: 1,
            centerMode: false,
            infinite: false,
            dots: true,
            arrows: false
        });

    }
});