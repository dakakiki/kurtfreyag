// assets/js/layout_references.js

/*
 * Block - References / Projects.
 *
 * Three jobs: the group filter replaces the grid, the load more button
 * appends to it, and a tap on a card opens its details. Everything the
 * requests need is read from data attributes on the block, so nothing here
 * depends on wp_localize_script landing at the right hook priority.
 */
(function () {

    "use strict";

    var block = document.querySelector("[data-references-block]");

    if (!block) {
        return;
    }

    var grid = block.querySelector("[data-references-grid]");
    var empty = block.querySelector("[data-references-empty]");
    var moreWrap = block.querySelector("[data-references-more-wrap]");
    var moreBtn = block.querySelector("[data-references-more]");
    var status = block.querySelector(".references__status");
    var pills = Array.prototype.slice.call(block.querySelectorAll("[data-reference-term]"));
    var select = block.querySelector("[data-reference-select]");

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

    var term = block.getAttribute("data-term") || "";
    var page = parseInt(block.getAttribute("data-page"), 10) || 1;
    var busy = false;

    function say(message) {
        if (status) {
            status.textContent = message || "";
        }
    }

    function setLoading(state) {
        busy = state;

        block.setAttribute("aria-busy", state ? "true" : "false");

        if (moreBtn) {
            moreBtn.disabled = state;
            moreBtn.classList.toggle("is-loading", state);
            moreBtn.textContent = state ? labels.loading : labels.more;
        }

        pills.forEach(function (pill) {
            pill.disabled = state;
        });

        if (select) {
            select.disabled = state;
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

        if (term) {
            url.searchParams.set("ref_group", term);
        } else {
            url.searchParams.delete("ref_group");
        }

        /* Page is a scroll position, not a destination - it stays out. */
        url.searchParams.delete("ref_page");

        window.history.replaceState({}, "", url.toString());
    }

    function markActive() {

        pills.forEach(function (pill) {

            var isActive = (pill.getAttribute("data-reference-term") || "") === term;

            pill.classList.toggle("is-active", isActive);
            pill.setAttribute("aria-pressed", isActive ? "true" : "false");
        });

        /* The two controls are alternate views of one value. */
        if (select) {
            select.value = term;
        }
    }

    function toNodes(html) {

        var holder = document.createElement("div");

        holder.innerHTML = String(html || "").trim();

        return Array.prototype.slice.call(holder.children);
    }

    function animate(nodes) {

        if (typeof window.initCardReveal !== "function" || !nodes.length) {
            return;
        }

        /*
         * The cards are already on screen by the time they arrive, so they
         * play at once. A temporary wrapper is not needed: the helper takes
         * any scope, and the grid itself still carries data-gsap-cards.
         */
        window.initCardReveal(grid, true);
    }

    function focusFirst(node) {

        if (!node) {
            return;
        }

        var control = node.querySelector("button");

        if (!control) {
            return;
        }

        control.setAttribute("tabindex", "-1");
        control.focus({ preventScroll: true });
    }

    function request(targetPage, targetTerm) {

        var body = new URLSearchParams();

        body.append("action", action);
        body.append("nonce", nonce);
        body.append("page", String(targetPage));
        body.append("term", targetTerm);

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

        request(page + 1, term).then(function (data) {

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

    /* ---------------------------------------------------------- filter */

    function filterTerm(nextTerm) {

        nextTerm = nextTerm || "";

        if (busy || nextTerm === term) {
            return;
        }

        setLoading(true);
        say("");

        request(1, nextTerm).then(function (data) {

            term = nextTerm;
            page = 1;

            markActive();
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

            /* Put the controls back on the group that is actually displayed. */
            markActive();
            say(labels.error);
        });
    }

    pills.forEach(function (pill) {

        pill.addEventListener("click", function () {
            filterTerm(pill.getAttribute("data-reference-term"));
        });
    });

    if (select) {

        select.addEventListener("change", function () {
            filterTerm(select.value);
        });
    }

    if (moreBtn) {
        moreBtn.addEventListener("click", loadMore);
    }

    /* ------------------------------------------------------ card details */

    /*
     * CSS already opens a card on hover and on focus. A touch screen has
     * neither, so the toggle is wired up here - and delegated, because cards
     * arrive from AJAX long after this runs.
     */
    block.addEventListener("click", function (e) {

        var toggle = e.target.closest("[data-reference-toggle]");

        if (!toggle) {
            return;
        }

        var card = toggle.closest("[data-reference-card]");

        if (!card) {
            return;
        }

        var open = !card.classList.contains("is-open");

        /* One card at a time, so a tap does not leave a trail of open ones. */
        block.querySelectorAll("[data-reference-card].is-open").forEach(function (other) {

            if (other === card) {
                return;
            }

            other.classList.remove("is-open");

            var otherToggle = other.querySelector("[data-reference-toggle]");

            if (otherToggle) {
                otherToggle.setAttribute("aria-expanded", "false");
            }
        });

        card.classList.toggle("is-open", open);
        toggle.setAttribute("aria-expanded", open ? "true" : "false");
    });
})();