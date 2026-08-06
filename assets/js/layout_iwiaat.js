// assets/js/layout_iwiaat.js

/*
 * Block - Items with icon and additional text: accordion animation.
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

    var panels = Array.prototype.slice.call(
        document.querySelectorAll(".layout-iwiaat .iwiaat__details")
    );

    if (!panels.length) {
        return;
    }

    var reduced = window.matchMedia("(prefers-reduced-motion: reduce)");

    panels.forEach(function (details) {

        var summary = details.querySelector("summary");
        var body = details.querySelector(".iwiaat__body");

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