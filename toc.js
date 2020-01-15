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
        var headers = root.querySelectorAll("h1, h2, h3, h4, h5, h6");

        var listNodeName = FAST_TOC.ordered ? "ol" : "ul"

        if (FAST_TOC.collapsible) {

            var currentLevel = null;
            var lastLevel = null;

            list.push("<" + listNodeName + " class='fast-toc-collapsible'>");
            // Make nested list, only works for correct hierarchy of headers
            
            headers.forEach(function(h, index) {
                
                if (h.getAttribute('data-fast-toc-ignore')) return;

                var level = parseInt(h.nodeName.substr(1), 10);
                lastLevel = currentLevel;
                currentLevel = level;

                var id = "fh-" + index;
                h.setAttribute("id", id);

                if (lastLevel !== null) {
                    if (currentLevel > lastLevel) {
                        list.push("<" + listNodeName + ">"); // TODO: multiply for bigger steps in level?
                    } else if (currentLevel < lastLevel) {
                        list.push("</" + listNodeName + "></li>"); // TODO: multiply for bigger steps in level?
                    } else {
                        list.push("</li>");
                    }
                }
                list.push("<li><a href='#" + id + "'>" + h.innerText + "</a>");
                
                hCounter += 1;
            });

            list.push("</" + listNodeName + ">");

        } else {
            // Simple, works always
            var levelStart = null;
            list.push("<ul>");
            headers.forEach(function(h, index) {
                
                if (h.getAttribute('data-fast-toc-ignore')) return;

                var id = "fh-" + index;
                var level = parseInt(h.nodeName.substr(1), 10);
                if (index === 0) {
                    levelStart = level;
                }

                list.push("<li class='fast-toc-" + (level - levelStart) + "'><a href='#" + id + "'>" + h.innerText + "</a></li>");
                h.setAttribute("id", id);
                hCounter += 1;
            });
            list.push("</ul>");
        }

        if (hCounter === 0 || FAST_TOC.minimal_header_count && FAST_TOC.minimal_header_count > hCounter) {
            return;
        }

        var el = document.createElement("div");
        el.className = "fast-toc";
        el.innerHTML = list.join("\n");
        root.insertBefore(el, root.firstChild);
    }

});