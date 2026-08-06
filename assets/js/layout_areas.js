// assets/js/layout_areas.js

/*
 * Block - Service areas: accordion animation.
 *
 * The markup uses <details>/<summary>, so the accordion already opens and
 * closes, is keyboard operable and is announced correctly with no script at
 * all. This only adds the movement: the toggle is taken over so the panel can
 * be measured and animated, and on close the `open` attribute is not removed
 * until the height has finished travelling to zero.
 */
(function () {

    "use strict";

    var DURATION = 300;

    /*
     * The select shown below the breakpoint.
     *
     * Rather than scrolling itself, it clicks the matching pill: custom.js
     * already knows how to clear the fixed bar and how to handle a block that
     * overflows above its own box, and one implementation is better than two.
     * The pill is hidden at this width, which does not stop the click.
     */
    var select = document.querySelector("[data-areas-select]");

    if (select) {

        select.addEventListener("change", function () {

            var hash = select.value;

            if (!hash) {
                return;
            }

            var link = document.querySelector('.areas__nav a[href="' + hash + '"]');

            if (link) {
                link.click();
            } else {
                window.location.hash = hash;
            }

            /* Back to the placeholder, so the same area can be chosen twice. */
            select.selectedIndex = 0;
        });
    }

    var panels = Array.prototype.slice.call(
        document.querySelectorAll(".layout-areas .areas__option")
    );

    if (!panels.length) {
        return;
    }

    var reduced = window.matchMedia("(prefers-reduced-motion: reduce)");

    panels.forEach(function (details) {

        var summary = details.querySelector("summary");
        var body = details.querySelector(".areas__option-body");

        if (!summary || !body) {
            return;
        }

        var animating = false;

        function settle() {

            body.classList.remove("is-animating");
            body.style.height = "";

            animating = false;
        }

        function animate(from, to, done) {

            animating = true;

            body.style.height = from + "px";

            /*
             * The class is added after the starting height is in place,
             * otherwise the browser has nothing to transition from.
             */
            window.requestAnimationFrame(function () {

                body.classList.add("is-animating");
                body.style.height = to + "px";
            });

            var finished = false;

            function end(e) {

                if (e && e.propertyName !== "height") {
                    return;
                }

                if (finished) {
                    return;
                }

                finished = true;

                body.removeEventListener("transitionend", end);

                settle();

                if (done) {
                    done();
                }
            }

            body.addEventListener("transitionend", end);

            /*
             * A transition that never fires - an interrupted animation, a
             * hidden tab - would otherwise leave the panel stuck at a fixed
             * height, so the end is also called on a timer.
             */
            window.setTimeout(end, DURATION + 80);
        }

        summary.addEventListener("click", function (e) {

            /* Without motion the native behaviour is exactly right. */
            if (reduced.matches) {
                return;
            }

            e.preventDefault();

            /* Clear any leftover state from an interrupted close. */
            details.classList.remove("is-closing");

            if (animating) {
                return;
            }

            if (details.open) {

                /*
                 * The arrow follows the [open] attribute, which is not removed
                 * until the panel has finished collapsing. This class turns it
                 * back at the same moment the height starts moving, so the two
                 * travel together as they do on the way open.
                 */
                details.classList.add("is-closing");

                animate(body.scrollHeight, 0, function () {

                    details.open = false;
                    details.classList.remove("is-closing");
                });
            } else {

                /* Open first, so the panel can be measured. */
                details.open = true;

                animate(0, body.scrollHeight);
            }
        });
    });
})();