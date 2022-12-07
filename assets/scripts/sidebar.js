(function () {
    'use strict';

    // Sidebar
    const menuBtn = document.querySelector("[data-menu]");
    const sidebar = document.querySelector("[data-sidebar]");

    if (menuBtn) {
        menuBtn.addEventListener("click", () => {
            const hidden = document.body.classList.toggle("menu-hidden");
            localStorage.setItem('quickdocs-menu-hidden', hidden);

            if (sidebar) {
                sidebar.toggleAttribute("inert", hidden);
            }
        })
    }
    if (document.body.classList.contains("menu-hidden") && sidebar) {
        sidebar.toggleAttribute("inert", true);
    }


    // Light/Dark Mode
    const colorschemeBtn = document.querySelector("[data-colorscheme]");

    const toggleColorScheme = () => {
        const darkMode = document.body.classList.toggle("scheme-dark");

        if (darkMode) {
            localStorage.setItem('quickdocs-colorscheme', 'dark');
        }
        else {
            localStorage.setItem('quickdocs-colorscheme', 'light');
        }
    }

    if (colorschemeBtn) {
        colorschemeBtn.addEventListener("click", toggleColorScheme);
    }



    // Search

    const searchInput = document.querySelector("[data-search]");
    const searchResults = document.querySelector("[data-search-results]");
    const searchBtn = document.querySelector("[data-search-open]");
    const searchModal = document.querySelector("[data-search-modal]");

    const searchFetch = async query => {
        const resp = await fetch(`${quickdocs.rest_url}wp/v2/wrd_docs?search=${query}&per_page=6`, {
            method: 'GET',
            headers: new Headers({
                'X-WP-Nonce': quickdocs.rest_nonce
            })
        });

        const posts = await resp.json();

        return posts;
    }

    const searchResultTemplate = post => {
        const template = `
            <a class="result-link" href="${post.link}">
                <strong class="result-title">${post.title.rendered}</strong>

                <p class="result-desc">${post.search_query_highlight}</p>
            </a>`;

        const element = document.createElement("li");

        element.classList.add("result");
        element.setAttribute("role", "option");
        element.setAttribute("aria-selected", "false");

        element.addEventListener('mouseover', () => searchPsuedoFocusSet(element));

        element.innerHTML = template;

        return element;
    }

    const searchPsuedoFocusSet = newFocused => {
        const oldFocused = searchResults.querySelector("[aria-selected='true']");

        oldFocused?.setAttribute("aria-selected", "false");
        newFocused?.setAttribute("aria-selected", "true");

        newFocused?.scrollIntoView();
    }

    const searchPsuedoFocusMove = next => {
        const focused = searchResults.querySelector("[aria-selected='true']");
        let newFocused = next ? focused?.nextElementSibling : focused?.previousElementSibling;

        if (!newFocused) {
            newFocused = searchResults.firstElementChild;
        }

        searchPsuedoFocusSet(newFocused);
    }

    const searchPsuedoFocusEnter = e => {
        const focused = searchResults.querySelector("[aria-selected='true']");

        if (focused?.firstElementChild) {
            e.preventDefault();
            focused.firstElementChild.click();
        }
    }

    const searchNavigate = e => {
        switch (e.key) {
            case "ArrowDown":
                searchPsuedoFocusMove(true);
                break;

            case "ArrowUp":
                searchPsuedoFocusMove(false);
                break;

            case "Enter":
                searchPsuedoFocusEnter(e);
                break;
        }
    }

    const searchEvent = async e => {
        if (e.target.value.length == 0) {
            searchResults.replaceChildren();
            return;
        }

        const posts = await searchFetch(e.target.value);
        const children = [];

        for (const post of posts) {
            const el = searchResultTemplate(post);
            children.push(el);
        }

        searchResults.replaceChildren(...children);
    }

    searchInput?.addEventListener('keydown', searchNavigate);
    searchInput?.addEventListener('input', searchEvent);

    searchBtn?.addEventListener('click', () => searchModal.showModal());
    searchModal?.addEventListener('click', e => e.target == searchModal && searchModal.close());

    window.addEventListener('keydown', e => {
        if (e.key == "k" && (e.metaKey || e.ctrlKey)) {
            e.preventDefault();
            searchModal.showModal()
        }
    });
})();