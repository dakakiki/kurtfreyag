// assets/js/layout_teams.js

/*
 * Block - Teams: one slider per group.
 *
 * Before this runs the row is a plain grid, so the members are readable with
 * no script at all. Slick takes over per group, and the sky panel on the right
 * - the hint drawn in the XD - is wired up as the control that moves the row
 * on.
 *
 * One control, two jobs: it advances while there is more to see, and turns
 * back once the end is reached, so a visitor can walk a group in both
 * directions without a second panel taking up room beside the cards.
 */
jQuery(function ($) {

    var $rows = $(".layout-teams .teams__row");

    if (!$rows.length) {
        return;
    }

    $rows.each(function () {

        var $row = $(this);
        var $slider = $row.find("[data-teams-slider]");
        var $next = $row.find("[data-teams-next]");

        if (!$slider.length || $slider.hasClass("slick-initialized")) {
            return;
        }

        /* Which way the control is currently pointing. */
        var goingBack = false;

        var labelNext = $next.attr("data-label-next") || $next.attr("aria-label") || "";
        var labelPrev = $next.attr("data-label-prev") || labelNext;

        $slider.on("init reInit afterChange", function (e, slick) {

            if (!$next.length || !slick) {
                return;
            }

            /*
             * With everything already on screen there is nothing to advance
             * to, and a panel covering the last card would only be in the way.
             */
            var fits = slick.slideCount <= slick.options.slidesToShow;

            $next.prop("hidden", fits);

            if (fits) {
                return;
            }

            var current = slick.currentSlide;
            var last = slick.slideCount - slick.options.slidesToShow;

            /*
             * Turn back at the end and forward again at the start. In between
             * the direction is left alone, so a visitor walking backwards is
             * not flipped round after a single step.
             */
            if (current >= last) {
                goingBack = true;
            } else if (current <= 0) {
                goingBack = false;
            }

            $next.toggleClass("is-prev", goingBack);
            $next.attr("aria-label", goingBack ? labelPrev : labelNext);
        });

        $slider.slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            infinite: false,
            dots: false,
            arrows: false,

            /*
             * The breakpoints match the SCSS fallback grid, so the row does
             * not change shape when Slick initialises.
             */
            responsive: [
                {
                    breakpoint: 992,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1
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

        $next.on("click", function () {
            $slider.slick(goingBack ? "slickPrev" : "slickNext");
        });
    });
});