/*
* Custom JS
*/

(function($) {



})(jQuery);


document.addEventListener("DOMContentLoaded", () => {
    const navLinks = document.querySelectorAll('.nav-wrap a[href*="#"]');
    if (!navLinks.length) return;

    const STORAGE_KEY = "pendingHomepageAnchor";

    const getScrollOffset = () => {
        const header = document.querySelector("header");
        const headerHeight = header ? header.offsetHeight : 0;

        return headerHeight + 50;
    };

    const normalizeHash = (href) => {
        if (!href) return "";

        try {
            const url = new URL(href, window.location.origin);
            return url.hash || "";
        } catch (e) {
            const hashIndex = href.indexOf("#");
            return hashIndex !== -1 ? href.substring(hashIndex) : "";
        }
    };

    const getUrlObject = (href) => {
        try {
            return new URL(href, window.location.origin);
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

    const scrollToHash = (hash, duration = 600) => {
        if (!hash) return;

        const target = document.querySelector(hash);
        if (!target) return;

        const offset = getScrollOffset();
        const targetY = target.getBoundingClientRect().top + window.pageYOffset - offset;

        smoothScrollTo(targetY, duration);
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

            const currentPath = window.location.pathname.replace(/\/+$/, "") || "/";
            const targetPath = url.pathname.replace(/\/+$/, "") || "/";

            setActiveByHash(hash);

            if (currentPath === targetPath) {
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

    timeline
      .fromTo(rspMenu,
        { xPercent: 100 },
        { xPercent: 0, duration: 0.5 * m() })
      .fromTo(rspHeader,
        { autoAlpha: 0, y: -12 },
        { autoAlpha: 1, y: 0, duration: 0.4 * m() }, 0.15 * m())
      .fromTo(rspItems,
        { autoAlpha: 0, x: 28 },
        { autoAlpha: 1, x: 0, duration: 0.45 * m(), stagger: 0.06 * m() }, 0.2 * m())
      .fromTo(rspFooter,
        { autoAlpha: 0 },
        { autoAlpha: 1, duration: 0.4 * m() }, 0.35 * m());

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

    /* The exit mirrors the entrance but runs tighter and in reverse order,
       so the panel starts sliding while the last items are still leaving. */
    timeline
      .to(rspFooter,
        { autoAlpha: 0, duration: 0.15 * m(), ease: "power1.in" }, 0)
      .to(rspItems,
        {
          autoAlpha: 0,
          x: 20,
          duration: 0.25 * m(),
          ease: "power1.in",
          stagger: { each: 0.035 * m(), from: "end" }
        }, 0.05 * m())
      .to(rspHeader,
        { autoAlpha: 0, y: -10, duration: 0.2 * m(), ease: "power1.in" }, 0.1 * m())
      .to(rspMenu,
        { xPercent: 100, duration: 0.35 * m(), ease: "power2.in" }, 0.18 * m());
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