(function(win) {

    var fast_toc_show_counter = document.getElementById("fast_toc_show_counter");
    var showNumberInputs = ['fast_toc_counter_style', 'fast_toc_nested_items', 'fast_toc_item_separator'];

    win.onShowNumbersChange = function() {

        if (!fast_toc_show_counter.checked) {
            showNumberInputs.forEach(function(key) {
                //document.getElementById(key).checked = false;
                document.getElementById(key).setAttribute("disabled", "disabled");
            });
        } else {
            showNumberInputs.forEach(function(key) {
                //document.getElementById(key).checked = true;
                document.getElementById(key).removeAttribute("disabled");
            });
        }
    };
    
    fast_toc_show_counter.addEventListener("change", win.onShowNumbersChange);
    win.onShowNumbersChange();

}(window));