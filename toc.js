window.addEventListener("DOMContentLoaded", function() {
    if (FAST_TOC && FAST_TOC.show_toc) {

        var fastTocDiv = null;

        FAST_TOC.onListCollapserClick = function(e) {
            e.preventDefault();
            var el = e.target;
            if (fastTocDiv.classList.contains("fast-toc-collapsed")) {
                el.innerText = "-";
                fastTocDiv.classList.remove("fast-toc-collapsed");
            } else {
                el.innerText = "+";
                fastTocDiv.classList.add("fast-toc-collapsed");
            }
        };

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

        var listClassName = ["fast-toc-root-list"];

        if (FAST_TOC.show_counter) {
            listClassName.push("fast-toc-show-counter");
        }

        //if (FAST_TOC.list_type === "flat") {

            // // Simple, works always
            // var levelStart = null;
            // list.push("<ol class='" + listClassName.join(" ") + "'>");
            // headers.forEach(function(h, index) {
                
            //     if (h.getAttribute('data-fast-toc-ignore')) return;

            //     var id = "fh-" + index;
            //     var level = parseInt(h.nodeName.substr(1), 10);
            //     if (index === 0) {
            //         levelStart = level;
            //     }

            //     list.push("<li class='fast-toc-" + (level - levelStart) + "'><a href='#" + id + "'>" + h.innerText + "</a></li>");
            //     h.setAttribute("id", id);
            //     hCounter += 1;
            // });
            // list.push("</ol>");

        //} else {

            var listClickHandler = "";

            listClassName.push("fast-toc-" + FAST_TOC.list_type);

            var listItemsCollapsible = FAST_TOC.list_type.indexOf("collapsible_") === 0;

            if (listItemsCollapsible) {
                listClassName.push("fast-toc-collapsible");
                listClickHandler = "onclick='FAST_TOC.onListItemClick(event);' onkeypress='FAST_TOC.onListItemClick(event);'";

                FAST_TOC.onListItemClick = function(e) {
                    if (e.target.nodeName.toLowerCase() === "li") {
                        e.preventDefault();
                        var li = e.target;
                        if (li.classList.contains("fast-toc-has-child-list")) {
                            if (li.classList.contains("fast-toc-expanded")) {
                                li.classList.remove("fast-toc-expanded");
                            } else {
                                li.classList.add("fast-toc-expanded");
                            }
                        }
                    }
                };

            }

            var currentLevel = null;
            var lastLevel = null;

            list.push("<ol " + listClickHandler + " class='" + listClassName.join(" ") + "'>");
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
                        var levelDiff = currentLevel - lastLevel;
                        var tabIndex = listItemsCollapsible ? "tabindex='0'" : "";
                        list.push(list.pop().replace("<li>", "<li " + tabIndex + " class='fast-toc-has-child-list " + (FAST_TOC.list_type === "collapsible_expanded" ? "fast-toc-expanded" : "") + "'>"));
                        for (var i = 0; i < levelDiff; i++) {
                            list.push("<ol>");
                        }
                    } else if (currentLevel < lastLevel) {
                        var levelDiff = lastLevel - currentLevel;
                        for (var i = 0; i < levelDiff; i++) {
                            list.push("</ol></li>");
                        }
                    } else {
                        list.push("</li>");
                    }
                }

                list.push("<li><a href='#" + id + "'>" + h.innerText + "</a>");
                
                hCounter += 1;
            });

            list.push("</ol>");
        //}

        if (hCounter === 0 || FAST_TOC.minimal_header_count && FAST_TOC.minimal_header_count > hCounter) {
            return;
        }

        fastTocDiv = document.createElement("div");
        fastTocDiv.classList.add("fast-toc");
        if (FAST_TOC.collapsible !== "not_collapsible") {
            var collapsibleChar = "-";
            if (FAST_TOC.collapsible === "collapsible_collapsed") {
                fastTocDiv.classList.add("fast-toc-collapsed");
                collapsibleChar = "+";
            }
            fastTocDiv.innerHTML = "<a onkeypress='FAST_TOC.onListCollapserClick(event);' onclick='FAST_TOC.onListCollapserClick(event);' class='fast-toc-list-collapser' tabindex='0'>" + collapsibleChar + "</a>" + list.join("\n");
        } else {
            fastTocDiv.innerHTML = list.join("\n");
        }
        root.insertBefore(fastTocDiv, root.firstChild);
    }

});