(function(win) {

    var fast_toc_show_counter = document.getElementById("fast_toc_show_counter");

    win.onListTypeChange = function(el) {

        if (!el) return;

        if (el.value === "flat") {
            fast_toc_show_counter.checked = false;
            fast_toc_show_counter.setAttribute("disabled", "disabled");
        } else {
            fast_toc_show_counter.removeAttribute("disabled");
        }
    };

    win.onListTypeChange(document.querySelector("input[name='fast_toc_list_type']:checked"));

}(window));