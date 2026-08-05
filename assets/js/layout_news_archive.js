// assets/js/layout_news_archive.js

/*
 * Block - News (archive).
 *
 * Two controls, one request: the year pills replace the list, the load more
 * button appends to it. Everything the request needs is read from data
 * attributes on the block, so nothing here depends on wp_localize_script
 * landing at the right hook priority.
 */
(function () {

    "use strict";

    var block = document.querySelector("[data-news-block]");

    if (!block) {
        return;
    }

    var grid = block.querySelector("[data-news-grid]");
    var empty = block.querySelector("[data-news-empty]");
    var moreWrap = block.querySelector("[data-news-more-wrap]");
    var moreBtn = block.querySelector("[data-news-more]");
    var status = block.querySelector(".news-archive__status");
    var yearBtns = Array.prototype.slice.call(block.querySelectorAll("[data-news-year-btn]"));
    var yearSelect = block.querySelector("[data-news-year-select]");

    var ajaxUrl = block.getAttribute("data-ajax-url");
    var action = block.getAttribute("data-action");
    var nonce = block.getAttribute("data-nonce");

    if (!grid || !ajaxUrl || !action || !nonce) {
        return;
    }

    var labels = {
        more: moreBtn ? moreBtn.getAttribute("data-label-more") || moreBtn.textContent : "",
        loading: moreBtn ? moreBtn.getAttribute("data-label-loading") || "" : "",
        error: moreBtn ? moreBtn.getAttribute("data-label-error") || "" : ""
    };

    var year = parseInt(block.getAttribute("data-year"), 10) || 0;
    var page = parseInt(block.getAttribute("data-page"), 10) || 1;
    var busy = false;

    function say(message) {
        if (status) {
            status.textContent = message || "";
        }
    }

    function setLoading(state) {
        busy = state;

        block.classList.toggle("is-loading", state);
        block.setAttribute("aria-busy", state ? "true" : "false");

        if (moreBtn) {
            moreBtn.disabled = state;
            moreBtn.classList.toggle("is-loading", state);
            moreBtn.textContent = state ? labels.loading : labels.more;
        }

        yearBtns.forEach(function (btn) {
            btn.disabled = state;
        });

        if (yearSelect) {
            yearSelect.disabled = state;
        }
    }

    function showMore(visible) {
        if (moreWrap) {
            moreWrap.hidden = !visible;
        }
    }

    function showEmpty(visible) {
        if (empty) {
            empty.hidden = !visible;
        }
    }

    /*
     * Keep the address bar in step with what is on screen. The state is a
     * plain query string the server already understands, so a copied or
     * reloaded URL lands on the same filtered list.
     */
    function syncUrl() {

        if (!window.history || !window.history.replaceState) {
            return;
        }

        var url = new URL(window.location.href);

        if (year) {
            url.searchParams.set("news_year", String(year));
        } else {
            url.searchParams.delete("news_year");
        }

        /* Page is a scroll position, not a destination - it stays out. */
        url.searchParams.delete("news_page");

        window.history.replaceState({}, "", url.toString());
    }

    function animate(nodes) {

        if (typeof window.initFadeUpAnimations !== "function") {
            return;
        }

        /*
         * Immediately rather than on scroll: these cards are already in view.
         * Passing true also skips ScrollTrigger.refresh(), which recalculates
         * every trigger on the page and can throw the viewport to the top.
         */
        nodes.forEach(function (node) {
            window.initFadeUpAnimations(node, true);
        });
    }

    function toNodes(html) {

        var holder = document.createElement("div");

        holder.innerHTML = String(html || "").trim();

        return Array.prototype.slice.call(holder.children);
    }

    function focusFirst(node) {

        if (!node) {
            return;
        }

        var link = node.querySelector("a");

        if (!link) {
            return;
        }

        link.setAttribute("tabindex", "-1");
        link.focus({ preventScroll: true });
    }

    function request(targetPage, targetYear) {

        var body = new URLSearchParams();

        body.append("action", action);
        body.append("nonce", nonce);
        body.append("page", String(targetPage));
        body.append("year", String(targetYear));

        return fetch(ajaxUrl, {
            method: "POST",
            credentials: "same-origin",
            headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
            body: body.toString()
        }).then(function (response) {

            if (!response.ok) {
                throw new Error("HTTP " + response.status);
            }

            return response.json();
        }).then(function (payload) {

            if (!payload || !payload.success || !payload.data) {
                throw new Error("Unexpected response");
            }

            return payload.data;
        });
    }

    /* ------------------------------------------------------- load more */

    function loadMore() {

        if (busy) {
            return;
        }

        setLoading(true);
        say("");

        request(page + 1, year).then(function (data) {

            page = data.page || page + 1;

            var nodes = toNodes(data.html);
            var scrollY = window.scrollY || window.pageYOffset || 0;

            nodes.forEach(function (node) {
                grid.appendChild(node);
            });

            animate(nodes);

            if ((window.scrollY || window.pageYOffset || 0) !== scrollY) {
                window.scrollTo(0, scrollY);
            }

            setLoading(false);
            showMore(!!data.has_more);

            focusFirst(nodes[0]);
        }).catch(function () {

            setLoading(false);
            say(labels.error);
        });
    }

    /* ---------------------------------------------------- year filter */

    function markActiveYear() {

        yearBtns.forEach(function (btn) {

            var isActive = (parseInt(btn.getAttribute("data-year"), 10) || 0) === year;

            btn.classList.toggle("is-active", isActive);
            btn.setAttribute("aria-pressed", isActive ? "true" : "false");
        });

        /* The two controls are alternate views of one value, so keep them in step. */
        if (yearSelect) {
            yearSelect.value = String(year);
        }
    }

    function filterYear(nextYear) {

        if (busy || nextYear === year) {
            return;
        }

        setLoading(true);
        say("");

        request(1, nextYear).then(function (data) {

            year = nextYear;
            page = 1;

            markActiveYear();
            syncUrl();

            var nodes = toNodes(data.html);

            /* Replace rather than append: this is a new list, not more of one. */
            grid.innerHTML = "";

            nodes.forEach(function (node) {
                grid.appendChild(node);
            });

            animate(nodes);

            setLoading(false);
            showMore(!!data.has_more);
            showEmpty(nodes.length === 0);

            focusFirst(nodes[0]);
        }).catch(function () {

            setLoading(false);

            /* Put the controls back on the year that is actually displayed. */
            markActiveYear();
            say(labels.error);
        });
    }

    yearBtns.forEach(function (btn) {

        btn.addEventListener("click", function () {
            filterYear(parseInt(btn.getAttribute("data-year"), 10) || 0);
        });
    });

    if (yearSelect) {

        yearSelect.addEventListener("change", function () {
            filterYear(parseInt(yearSelect.value, 10) || 0);
        });
    }

    if (moreBtn) {
        moreBtn.addEventListener("click", loadMore);
    }
})();