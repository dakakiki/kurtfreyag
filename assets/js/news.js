// assets/js/news.js

/*
 * News archive - load more.
 *
 * The button is a real link to the next archive page, so the list is fully
 * browsable without this script. Here it is intercepted and the next page of
 * cards is appended instead.
 */
(function () {

    "use strict";

    var config = window.kfaNews;

    if (!config || !config.ajaxUrl) {
        return;
    }

    var button = document.querySelector("[data-news-more]");
    var grid = document.querySelector("[data-news-grid]");

    if (!button || !grid) {
        return;
    }

    var status = document.querySelector(".news-archive__status");
    var page = parseInt(button.getAttribute("data-page"), 10) || 1;
    var year = parseInt(button.getAttribute("data-year"), 10) || 0;
    var busy = false;

    function say(message) {
        if (status) {
            status.textContent = message || "";
        }
    }

    function setBusy(state) {
        busy = state;

        button.setAttribute("aria-busy", state ? "true" : "false");
        button.classList.toggle("is-loading", state);
        button.textContent = state ? config.i18n.loading : config.i18n.more;
    }

    /*
     * Move focus to the first card that just arrived. Without this, keyboard
     * and screen reader users are left on a button whose new content sits
     * above them, with no indication that anything happened.
     */
    function focusFirstNew(firstNode) {

        if (!firstNode) {
            return;
        }

        var link = firstNode.querySelector("a");

        if (!link) {
            return;
        }

        link.setAttribute("tabindex", "-1");
        link.focus({ preventScroll: true });
    }

    function append(html) {

        var holder = document.createElement("div");

        holder.innerHTML = html.trim();

        var added = Array.prototype.slice.call(holder.children);

        if (!added.length) {
            return null;
        }

        added.forEach(function (node) {
            grid.appendChild(node);
        });

        /* Let the scroll animations pick up whatever just landed. */
        if (typeof window.initFadeUpAnimations === "function") {
            added.forEach(function (node) {
                window.initFadeUpAnimations(node);
            });
        }

        return added[0];
    }

    function load() {

        if (busy) {
            return;
        }

        setBusy(true);
        say("");

        var body = new URLSearchParams();

        body.append("action", config.action);
        body.append("nonce", config.nonce);
        body.append("page", String(page + 1));
        body.append("year", String(year));

        fetch(config.ajaxUrl, {
            method: "POST",
            credentials: "same-origin",
            headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
            body: body.toString()
        })
            .then(function (response) {

                if (!response.ok) {
                    throw new Error("HTTP " + response.status);
                }

                return response.json();
            })
            .then(function (payload) {

                if (!payload || !payload.success || !payload.data) {
                    throw new Error("Unexpected response");
                }

                page = payload.data.page || page + 1;

                var first = append(payload.data.html || "");

                if (payload.data.has_more) {
                    setBusy(false);

                    button.setAttribute("data-page", String(page));
                    button.setAttribute("href", button.getAttribute("href").replace(/\/page\/\d+/, "/page/" + (page + 1)));
                } else {
                    /* Nothing left to fetch: the control has served its purpose. */
                    button.parentNode.removeChild(button);
                }

                focusFirstNew(first);
            })
            .catch(function () {

                setBusy(false);

                /*
                 * The link still points at the next page, so a failed request
                 * leaves the visitor with a working way forward.
                 */
                say(config.i18n.error);
            });
    }

    button.addEventListener("click", function (event) {

        event.preventDefault();
        load();
    });
})();