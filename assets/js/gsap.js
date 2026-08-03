// assets/js/gsap.js

import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

window.gsap = gsap;

/*
 * Tells the inline head script that the animation bundle is alive, so it keeps
 * the `js` flag and the CSS is allowed to hide elements before they animate.
 */
window.__animationsReady = true;

/**
 * Respect the operating system "reduce motion" setting.
 * When it is on, content is shown in its final state and nothing animates.
 */
const reduceMotionQuery = window.matchMedia("(prefers-reduced-motion: reduce)");

function prefersReducedMotion() {
    return reduceMotionQuery.matches;
}

window.runGsapFadeUp = function (scope = document) {
    const fadeUpItems = scope.querySelectorAll(".gsap-fade-up");
    const fadeUpBtns = scope.querySelectorAll(".gsap-fade-up-btn");

    const reduced = prefersReducedMotion();

    if (fadeUpItems.length) {
        gsap.killTweensOf(fadeUpItems);
        gsap.to(fadeUpItems, {
            y: 0,
            autoAlpha: 1,
            duration: reduced ? 0 : 1,
            stagger: reduced ? 0 : 0.15,
            ease: "power2.out"
        });
    }

    if (fadeUpBtns.length) {
        gsap.killTweensOf(fadeUpBtns);
        gsap.to(fadeUpBtns, {
            y: 0,
            autoAlpha: 1,
            duration: reduced ? 0 : 1,
            ease: "power2.out"
        });
    }
};

window.initFadeUpAnimations = function (scope = document, animateNow = false) {
    const animations = [
        { name: "fade-up", group: "group-fade-up", from: { y: 40 } },
        { name: "fade-left", group: "group-fade-left", from: { x: -50 } },
        { name: "fade-right", group: "group-fade-right", from: { x: 50 } }
    ];

    const reduced = prefersReducedMotion();

    animations.forEach(({ name, group: groupName, from }) => {
        const initializedClass = `${name}-initialized`;
        const itemSelector = `.${name}:not(.${initializedClass})`;
        let groups = Array.from(scope.querySelectorAll(`.${groupName}`));

        if (scope.classList && scope.classList.contains(groupName)) {
            groups.unshift(scope);
        }

        groups.forEach((group) => {
            const items = Array.from(group.querySelectorAll(itemSelector))
                .filter((item) => item.closest(`.${groupName}`) === group);

            if (!items.length) return;

            items.forEach((item) => item.classList.add(initializedClass));

            /* Reduced motion: the markup is already in its final state. */
            if (reduced) return;

            gsap.from(items, {
                ...from,
                autoAlpha: 0,
                duration: 1,
                stagger: 0.2,
                ease: "power2.out",
                clearProps: "opacity,visibility,transform",
                scrollTrigger: animateNow ? null : {
                    trigger: group,
                    start: "top bottom",
                    toggleActions: "play none none none"
                }
            });
        });

        const singles = Array.from(scope.querySelectorAll(itemSelector));

        if (scope.matches && scope.matches(itemSelector)) {
            singles.unshift(scope);
        }

        singles
            .filter((item) => !item.closest(`.${groupName}`))
            .forEach((item) => {
                item.classList.add(initializedClass);

                if (reduced) return;

                gsap.from(item, {
                    ...from,
                    autoAlpha: 0,
                    duration: 1,
                    ease: "power2.out",
                    clearProps: "opacity,visibility,transform",
                    scrollTrigger: animateNow ? null : {
                        trigger: item,
                        start: "top bottom",
                        toggleActions: "play none none none"
                    }
                });
            });
    });

    if (!animateNow && !reduced) {
        ScrollTrigger.refresh();
    }
};

/**
 * Numbered process steps (layout_path).
 * Steps start dimmed and light up one after another.
 * The connecting line is optional and only animated when it exists.
 */
window.initPathAnimations = function (scope = document) {
    const selector = ".path__steps:not(.path-initialized)";

    const paths = Array.from(scope.querySelectorAll(selector));

    if (scope.matches && scope.matches(selector)) {
        paths.unshift(scope);
    }

    if (!paths.length) return;

    const reduced = prefersReducedMotion();

    paths.forEach((path) => {
        path.classList.add("path-initialized");

        const steps = gsap.utils.toArray(path.querySelectorAll(".path__step"));
        const lines = path.querySelectorAll(".path__step-line");

        if (!steps.length) return;

        /* Reduced motion: jump straight to the finished state. */
        if (reduced) {
            gsap.set(steps, { opacity: 1 });

            if (lines.length) {
                gsap.set(lines, { scaleX: 1 });
            }

            return;
        }

        gsap.set(steps, { opacity: 0.2 });

        if (lines.length) {
            gsap.set(lines, {
                scaleX: 0,
                transformOrigin: "left center"
            });
        }

        const tl = gsap.timeline({
            scrollTrigger: {
                trigger: path,
                start: "top bottom",
                once: true,
                immediateRender: true
            }
        });

        steps.forEach((step) => {
            const line = step.querySelector(".path__step-line");

            tl.to(step, {
                opacity: 1,
                duration: 0.35,
                ease: "power2.out"
            });

            if (line) {
                tl.to(line, {
                    scaleX: 1,
                    duration: 0.45,
                    ease: "power2.inOut"
                }, "-=0.1");
            }
        });
    });
};

function initializeScrollAnimations() {
    window.initFadeUpAnimations(document);
    window.initPathAnimations(document);

    ScrollTrigger.refresh();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeScrollAnimations);
} else {
    initializeScrollAnimations();
}

/**
 * Trigger positions are measured before images and webfonts finish loading,
 * so they have to be recalculated once the page has settled.
 */
window.addEventListener("load", () => {
    ScrollTrigger.refresh();
});

if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(() => {
        ScrollTrigger.refresh();
    });
}