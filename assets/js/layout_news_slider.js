// assets/js/layout_news_slider.js

jQuery(function ($) {

    var $sliders = $('.news__slides');

    if (!$sliders.length) {
        return;
    }

    $sliders.each(function () {

        var $el = $(this);

        if ($el.hasClass('slick-initialized')) {
            return;
        }

        $el.slick({
            slidesToShow: 3,
            slidesToScroll: 3,
            centerMode: false,
            infinite: false,
            dots: true,
            arrows: false,

            /*
             * Three cards only fit on the desktop frame, and they move a full
             * page at a time - nine posts give three clean pages. The
             * breakpoints match the SCSS fallback grid, so the layout does not
             * jump when Slick takes over, and each one scrolls by as many
             * cards as it shows.
             */
            responsive: [
                {
                    breakpoint: 992,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                },
                {
                    breakpoint: 641,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        });
    });
});