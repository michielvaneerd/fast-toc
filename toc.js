window.addEventListener("DOMContentLoaded", function() {
    if (FAST_TOC && FAST_TOC.show_toc) {

        var root = FAST_TOC.root_selector ? document.querySelector(FAST_TOC.root_selector) : document.body;

        if (FAST_TOC.selector_ignore) {
            root.querySelectorAll(FAST_TOC.selector_ignore).forEach(function(h) {
                h.setAttribute("data-fast-toc-ignore", "true");
            });
        }

        var list = [];
        if (FAST_TOC.title) {
            list.push("<div class='fast-toc-title'>" + FAST_TOC.title + "</div>");
        }
        var hCounter = 0;
        list.push("<ul>");
        root.querySelectorAll("h1, h2, h3, h4, h5, h6").forEach(function(h, index) {
            
            if (h.getAttribute('data-fast-toc-ignore')) return;

            var id = "fast-toc-" + index;
            list.push("<li class='fast-toc-" + h.nodeName.toLocaleLowerCase() + "'><a href='#" + id + "'>" + h.innerText + "</a></li>");
            h.setAttribute("id", id);
            hCounter += 1;
        });
        list.push("</ul>");

        if (hCounter === 0 || FAST_TOC.minimal_header_count && FAST_TOC.minimal_header_count > hCounter) {
            return;
        }

        var el = document.createElement("div");
        el.className = "fast-toc";
        el.innerHTML = list.join("\n");
        root.insertBefore(el, root.firstChild);
    }

});