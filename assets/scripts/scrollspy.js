(function () {
    'use strict';

    var headings = document.querySelectorAll("[data-article] h2, [data-article] h3, [data-article] h4");
    var sections = {};
    var i = 0;

    Array.prototype.forEach.call(headings, function (e) {
        sections[e.id] = e.offsetTop;
    });

    window.onscroll = function () {
        var scrollPosition = document.documentElement.scrollTop || document.body.scrollTop;

        for (const id in sections) {
            if (sections[id] <= scrollPosition && id.length) {
                const selector = `a[href*=${id}]`;
                document.querySelector('.current')?.classList.remove('current');
                document.querySelector(selector)?.classList.add('current');
            }
        }
    };
})();
