/*
* Custom JS
*/

(function($) {



})(jQuery);


document.addEventListener("DOMContentLoaded", () => {
    /*
     * Every in-page link that should land below the fixed bar.
     *
     * The main navigation was the only one wired up, so links added by a
     * layout - the service area jump list, for instance - fell through to the
     * browser's own jump and landed underneath the header. Adding
     * data-anchor-scroll to a wrapper is enough to opt a new block in.
     */
    const navLinks = document.querySelectorAll(
        '.nav-wrap a[href*="#"], .areas__nav a[href*="#"], [data-anchor-scroll] a[href*="#"]'
    );

    if (!navLinks.length) return;

    const STORAGE_KEY = "pendingHomepageAnchor";

    /* Breathing room between the header and whatever it scrolls to. */
    const SCROLL_GAP = 24;

    /*
     * How far above the target to stop.
     *
     * A target can ask for its own distance with scroll-margin-top, and that
     * value wins. It is the same property the browser honours when it jumps
     * without any script, so a pasted link and a clicked one land in exactly
     * the same place - and a block whose artwork overhangs its top edge, like
     * the icon on a service area card, can reserve the room it needs from its
     * own stylesheet instead of from here.
     */
    const getScrollOffset = (target) => {

        if (target) {
            const declared = parseFloat(
                window.getComputedStyle(target).scrollMarginTop
            );

            if (declared > 0) {
                return declared;
            }
        }

        const header = document.querySelector("header");

        /*
         * Measured on every jump rather than cached: the bar is 100 tall on
         * desktop and 64 below 1024, and a visitor can cross that width
         * without reloading.
         *
         * getBoundingClientRect() rather than offsetHeight because the value
         * is fractional at some zoom levels, and offsetHeight rounds it.
         */
        const headerHeight = header ? header.getBoundingClientRect().height : 0;

        return headerHeight + SCROLL_GAP;
    };

    /*
     * Relative links are resolved against the current page, not the origin.
     *
     * With the origin as the base, a bare "#anchor" resolved to "/" - so the
     * handler decided the target was on another page and sent the visitor to
     * the homepage. Menu links are absolute, which is why this only surfaced
     * once in-page jump links appeared.
     */
    const normalizeHash = (href) => {
        if (!href) return "";

        try {
            const url = new URL(href, window.location.href);
            return url.hash || "";
        } catch (e) {
            const hashIndex = href.indexOf("#");
            return hashIndex !== -1 ? href.substring(hashIndex) : "";
        }
    };

    const getUrlObject = (href) => {
        try {
            return new URL(href, window.location.href);
        } catch (e) {
            return null;
        }
    };

    const clearActiveStates = () => {
        document.querySelectorAll(".nav-wrap li").forEach((item) => {
            item.classList.remove(
                "is-active",
                "current-menu-item",
                "current_page_item",
                "current_page_parent",
                "page_item"
            );
        });
    };

    const setActiveByHash = (hash) => {
        if (!hash) return;

        clearActiveStates();

        navLinks.forEach((link) => {
            const linkHash = normalizeHash(link.getAttribute("href"));
            if (linkHash === hash) {
                const li = link.closest("li");
                if (li) li.classList.add("is-active");
            }
        });
    };

    const smoothScrollTo = (targetY, duration = 600) => {
        const startY = window.pageYOffset;
        const distance = targetY - startY;
        let startTime = null;

        const easeInOutCubic = (t) => {
            return t < 0.5
                ? 4 * t * t * t
                : 1 - Math.pow(-2 * t + 2, 3) / 2;
        };

        const animation = (currentTime) => {
            if (!startTime) startTime = currentTime;

            const time = currentTime - startTime;
            const progress = Math.min(time / duration, 1);
            const eased = easeInOutCubic(progress);

            window.scrollTo(0, startY + distance * eased);

            if (time < duration) {
                requestAnimationFrame(animation);
            }
        };

        requestAnimationFrame(animation);
    };

    /*
     * The highest point the target actually paints at.
     *
     * A block's own box is not always its visual top: the service areas hang
     * their symbol above the card's border, so scrolling to the card alone
     * left that symbol tucked under the header. Any descendant reaching
     * higher is taken into account, which works for anything that overflows
     * upwards without the script needing to know about it.
     */
    const getVisualTop = (target) => {
        let top = target.getBoundingClientRect().top;

        target.querySelectorAll("*").forEach((child) => {
            const rect = child.getBoundingClientRect();

            /* Skip anything not rendered - a collapsed panel, a hidden image. */
            if (!rect.width || !rect.height) return;

            if (rect.top < top) top = rect.top;
        });

        return top;
    };

    const scrollToHash = (hash, duration = 600) => {
        if (!hash) return;

        const target = document.querySelector(hash);
        if (!target) return;

        const offset = getScrollOffset(target);
        const targetY = getVisualTop(target) + window.pageYOffset - offset;

        /* Never past the top of the document. */
        smoothScrollTo(Math.max(0, targetY), duration);
    };

    const tryScrollFromStoredHash = () => {
        const storedHash = sessionStorage.getItem(STORAGE_KEY);
        if (!storedHash) return;

        sessionStorage.removeItem(STORAGE_KEY);
        setActiveByHash(storedHash);

        history.replaceState(null, "", storedHash);

        setTimeout(() => {
            scrollToHash(storedHash);
        }, 400);
    };

    navLinks.forEach((link) => {
        const li = link.closest("li");
        if (li) {
            li.classList.remove("current-menu-item", "current_page_item", "current_page_parent");
        }

        link.addEventListener("click", (e) => {
            const href = link.getAttribute("href");
            const hash = normalizeHash(href);
            const url = getUrlObject(href);

            if (!hash || !url) return;

            /* A bare hash is always this page, whatever the URL parsing says. */
            const sameDocument = href.trim().charAt(0) === "#";

            const currentPath = window.location.pathname.replace(/\/+$/, "") || "/";
            const targetPath = url.pathname.replace(/\/+$/, "") || "/";

            setActiveByHash(hash);

            if (sameDocument || currentPath === targetPath) {
                e.preventDefault();
                history.pushState(null, "", hash);

                setTimeout(() => {
                    scrollToHash(hash);
                }, 50);

                return;
            }

            e.preventDefault();
            sessionStorage.setItem(STORAGE_KEY, hash);

            window.location.href = url.pathname + url.search;
        });
    });

    if (window.location.hash) {
        setActiveByHash(window.location.hash);

        setTimeout(() => {
            scrollToHash(window.location.hash);
        }, 400);
    }

    tryScrollFromStoredHash();

    window.addEventListener("hashchange", () => {
        setActiveByHash(window.location.hash);
        scrollToHash(window.location.hash);
    });
});

const getScrollOffset = () => {
    const header = document.querySelector("header");
    const headerHeight = header ? header.offsetHeight : 0;

    return headerHeight; // + 25
};


document.addEventListener("DOMContentLoaded", function () {
  const hamburger = document.querySelector(".header__hamburger");
  const closeBtn = document.querySelector(".rsp__close");
  const rspMenu = document.querySelector(".rsp");
  const rspContent = document.querySelector(".rsp__content");

  if (!hamburger || !closeBtn || !rspMenu) {
    return;
  }

  const rspHeader = rspMenu.querySelector(".rsp__header");
  const rspFooter = rspMenu.querySelector(".rsp__footer");
  const rspItems = rspContent
    ? Array.from(rspContent.querySelectorAll(".nav-wrap__menu > li"))
    : [];

  const hasGsap = typeof window.gsap !== "undefined";
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

  /* Speed multiplier - collapses every duration to zero when motion is off. */
  function m() {
    return reduceMotion.matches ? 0 : 1;
  }

  /* Everything inside the panel that moves independently of the panel itself. */
  function panelParts() {
    return [rspHeader].concat(rspItems, [rspFooter]).filter(Boolean);
  }

  let menuOpen = false;
  let timeline = null;

  if (hasGsap) {
    /* GSAP drives the transform from here on, so the CSS transition would
       only fight it frame by frame. */
    rspMenu.classList.add("rsp--animated");
  }

  const FOCUSABLE = [
    "a[href]",
    "button:not([disabled])",
    "input:not([disabled])",
    "select:not([disabled])",
    "textarea:not([disabled])",
    '[tabindex]:not([tabindex="-1"])'
  ].join(",");

  function focusableItems() {
    return Array.from(rspMenu.querySelectorAll(FOCUSABLE)).filter(function (el) {
      return el.offsetParent !== null || el === closeBtn;
    });
  }

  function animateOpen() {

    if (!hasGsap) {
      return;
    }

    if (timeline) {
      timeline.kill();
    }

    const parts = panelParts();

    timeline = window.gsap.timeline({ defaults: { ease: "power3.out" } });

    /*
     * The panel drops in from above and settles, then the rows unfold
     * downwards one after another while the logo and the footer fade up.
     */
    timeline
      .fromTo(rspMenu,
        { yPercent: -100 },
        { yPercent: 0, duration: 0.45 * m() })
      .fromTo(rspHeader,
        { autoAlpha: 0, y: -16 },
        { autoAlpha: 1, y: 0, duration: 0.35 * m() }, 0.2 * m())
      .fromTo(rspItems,
        { autoAlpha: 0, y: 24, rotateX: -40, transformOrigin: "50% 0%" },
        {
          autoAlpha: 1,
          y: 0,
          rotateX: 0,
          duration: 0.5 * m(),
          stagger: 0.07 * m()
        }, 0.25 * m())
      .fromTo(rspFooter,
        { autoAlpha: 0, y: 16 },
        { autoAlpha: 1, y: 0, duration: 0.4 * m() }, 0.4 * m());

    return parts;
  }

  function animateClose(onDone) {
    if (!hasGsap) {
      onDone();
      return;
    }

    if (timeline) {
      timeline.kill();
    }

    const parts = panelParts();

    timeline = window.gsap.timeline({
      onComplete: function () {
        /* Hand the panel back to the stylesheet for the next open. */
        window.gsap.set(parts.concat([rspMenu]), { clearProps: "all" });
        onDone();
      }
    });

    /* The exit mirrors the entrance, tighter and in reverse order. */
    timeline
      .to(rspFooter,
        { autoAlpha: 0, y: 12, duration: 0.15 * m(), ease: "power1.in" }, 0)
      .to(rspItems,
        {
          autoAlpha: 0,
          y: 16,
          duration: 0.22 * m(),
          ease: "power1.in",
          stagger: { each: 0.04 * m(), from: "end" }
        }, 0.05 * m())
      .to(rspHeader,
        { autoAlpha: 0, y: -12, duration: 0.2 * m(), ease: "power1.in" }, 0.1 * m())
      .to(rspMenu,
        { yPercent: -100, duration: 0.35 * m(), ease: "power2.in" }, 0.18 * m());
  }

  /* Hiding the body scrollbar widens the viewport, which would shove the fixed
     header sideways. The measured width is handed to CSS to compensate. */
  function lockScroll() {
    const scrollbar = window.innerWidth - document.documentElement.clientWidth;

    document.documentElement.style.setProperty("--scrollbar-width", scrollbar + "px");
    document.body.classList.add("has-menu-open");
  }

  function unlockScroll() {
    document.body.classList.remove("has-menu-open");
    document.documentElement.style.removeProperty("--scrollbar-width");
  }

  function openMenu() {
    menuOpen = true;

    lockScroll();

    rspMenu.classList.remove("is-closing");
    rspMenu.classList.add("is-open");
    hamburger.setAttribute("aria-expanded", "true");

    animateOpen();

    // Move focus into the panel so the keyboard follows the visual change.
    closeBtn.focus();
  }

  function closeMenu(returnFocus) {
    menuOpen = false;

    hamburger.setAttribute("aria-expanded", "false");

    // Only pull focus back when the user closed it deliberately, so following
    // a menu link does not steal focus from the destination.
    if (returnFocus) {
      hamburger.focus();
    }

    animateClose(function () {
      rspMenu.classList.remove("is-open");
      rspMenu.classList.add("is-closing");

      /* Released at the end so the page cannot shift mid animation. */
      unlockScroll();
    });
  }

  hamburger.addEventListener("click", function () {
    if (menuOpen) {
      closeMenu(true);
    } else {
      openMenu();
    }
  });

  closeBtn.addEventListener("click", function () {
    closeMenu(true);
  });

  // Close on link click inside menu
  if (rspContent) {
    rspContent.addEventListener("click", function (e) {
      const link = e.target.closest("a");

      if (link) {
        closeMenu(false);
      }
    });
  }

  document.addEventListener("keydown", function (e) {
    if (!menuOpen) {
      return;
    }

    if (e.key === "Escape") {
      closeMenu(true);
      return;
    }

    // Keep tabbing inside the panel while it covers the page.
    if (e.key !== "Tab") {
      return;
    }

    const items = focusableItems();

    if (!items.length) {
      return;
    }

    const first = items[0];
    const last = items[items.length - 1];

    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  });
});

/*
* Submenus
*
* The parent link is left alone so it always navigates to its own page.
* Opening and closing is entirely the job of the toggle button next to it,
* which works the same in the fixed bar and inside the mobile panel.
*/
document.addEventListener("DOMContentLoaded", function () {

    var toggles = Array.prototype.slice.call(
        document.querySelectorAll(".nav-wrap__toggle")
    );

    if (!toggles.length) {
        return;
    }

    function parentOf(toggle) {
        return toggle.closest("li");
    }

    /*
     * Inside the mobile panel the submenu expands in flow, so it needs a real
     * height to animate between. CSS cannot transition to auto, so the height
     * is written in pixels for the duration of the transition and handed back
     * to auto once it lands - otherwise a submenu that later changes size
     * would stay stuck at its old height.
     */
    function isInPanel(li) {
        return !!li.closest(".rsp");
    }

    /*
     * A single pending transitionend handler per submenu. Without this, a
     * close that interrupts an unfinished open would still receive the open
     * handler when the collapse finishes, and it would set the height back to
     * auto - leaving an empty gap the size of the submenu.
     */
    function clearPending(ul) {

        if (ul.heightHandler) {
            ul.removeEventListener("transitionend", ul.heightHandler);
            ul.heightHandler = null;
        }
    }

    function onHeightEnd(ul, done) {

        clearPending(ul);

        ul.heightHandler = function (e) {

            if (e.propertyName !== "height" || e.target !== ul) {
                return;
            }

            clearPending(ul);
            done();
        };

        ul.addEventListener("transitionend", ul.heightHandler);
    }

    function expand(ul) {

        onHeightEnd(ul, function () {

            /* Back to auto so later content changes are not clipped. */
            ul.style.height = "auto";
        });

        ul.style.height = ul.scrollHeight + "px";
    }

    function collapse(ul) {

        onHeightEnd(ul, function () {

            /* Hand the height back to the stylesheet. */
            ul.style.height = "";
        });

        /* From auto to a measured height, forced through layout, then to zero. */
        ul.style.height = ul.scrollHeight + "px";

        void ul.offsetHeight;

        ul.style.height = "0px";
    }

    function panelOf(li) {
        return li.querySelector(".sub-menu");
    }

    function close(li) {

        if (!li) {
            return;
        }

        if (li.classList.contains("is-open") && isInPanel(li)) {

            var ul = panelOf(li);

            if (ul) {
                collapse(ul);
            }
        }

        li.classList.remove("is-open");

        var toggle = li.querySelector(".nav-wrap__toggle");

        if (toggle) {
            toggle.setAttribute("aria-expanded", "false");
        }
    }

    function closeAll(except) {

        toggles.forEach(function (toggle) {

            var li = parentOf(toggle);

            if (li && li !== except) {
                close(li);
            }
        });
    }

    toggles.forEach(function (toggle) {

        toggle.addEventListener("click", function (e) {

            e.preventDefault();
            e.stopPropagation();

            var li = parentOf(toggle);

            if (!li) {
                return;
            }

            var open = !li.classList.contains("is-open");

            /*
             * Only one panel open at a time in the fixed bar. Inside the mobile
             * panel the rows behave as an accordion for the same reason.
             */
            closeAll(li);

            if (isInPanel(li)) {

                var ul = panelOf(li);

                if (ul) {
                    if (open) {
                        expand(ul);
                    } else {
                        collapse(ul);
                    }
                }
            }

            li.classList.toggle("is-open", open);
            toggle.setAttribute("aria-expanded", open ? "true" : "false");
        });
    });

    /* A click anywhere outside a menu closes whatever is open. */
    document.addEventListener("click", function (e) {

        if (!e.target.closest(".nav-wrap")) {
            closeAll(null);
        }
    });

    document.addEventListener("keydown", function (e) {

        if (e.key !== "Escape") {
            return;
        }

        var open = document.querySelector(".nav-wrap li.is-open");

        if (open) {
            var toggle = open.querySelector(".nav-wrap__toggle");

            closeAll(null);

            /* Send focus back to the control that opened the panel. */
            if (toggle) {
                toggle.focus();
            }
        }
    });

    /*
     * Tabbing out of an open submenu closes it, so the panel never lingers
     * over the page once the keyboard has moved on.
     */
    document.addEventListener("focusin", function (e) {

        var li = e.target.closest(".nav-wrap li.is-open");

        if (!li) {
            closeAll(null);
        }
    });
});


/*
* Footer accordions
*
* Opening hours and billing addresses collapse below 991px, matching the 414
* footer states. Above that width they are plain headings with the content
* always visible, so the toggle is only active while the query matches.
*/
document.addEventListener("DOMContentLoaded", function () {

    var blocks = Array.prototype.slice.call(
        document.querySelectorAll(".footer__block[data-footer-block]")
    );

    if (!blocks.length) {
        return;
    }

    var mq = window.matchMedia("(max-width: 991px)");

    function apply() {

        blocks.forEach(function (block) {

            var toggle = block.querySelector(".footer__toggle");
            var panel = block.querySelector(".footer__panel");

            if (!toggle || !panel) {
                return;
            }

            if (mq.matches) {
                block.classList.add("is-collapsible");
                toggle.removeAttribute("aria-disabled");
                toggle.setAttribute(
                    "aria-expanded",
                    block.classList.contains("is-open") ? "true" : "false"
                );
            } else {
                block.classList.remove("is-collapsible");
                toggle.setAttribute("aria-expanded", "true");
                toggle.setAttribute("aria-disabled", "true");
            }
        });
    }

    blocks.forEach(function (block) {

        var toggle = block.querySelector(".footer__toggle");

        if (!toggle) {
            return;
        }

        /* Preserve the authored default before the first apply() run. */
        if (toggle.getAttribute("aria-expanded") === "true") {
            block.classList.add("is-open");
        }

        toggle.addEventListener("click", function () {

            if (!mq.matches) {
                return;
            }

            var open = block.classList.toggle("is-open");

            toggle.setAttribute("aria-expanded", open ? "true" : "false");
        });
    });

    apply();

    if (typeof mq.addEventListener === "function") {
        mq.addEventListener("change", apply);
    } else if (typeof mq.addListener === "function") {
        mq.addListener(apply);
    }
});