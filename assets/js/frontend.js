var show_and_hide = "";

jQuery(document).ready(function (e) {
	jQuery(".fastfilter-checkbox").click(function() {
        applyFilter();
    });

    jQuery(".fastfilter-post-item").click(function() {
        var href = jQuery(this).data("href");
        location.href = href;
    });

    show_and_hide = jQuery("#show_and_hide_for_jquery").text();

    // Set up no results to work with invisible mode if necessary.
    if (show_and_hide == "invisible") {
        jQuery('.fastfilter-no-results').css("opacity", "0");
        jQuery('.fastfilter-no-results').show();
        
    }

    applyFilter();

    // Fix the current height of .entry-content so as things disappear it
    // doesn't resize.
    var entryContent = jQuery(".entry-content"); 
    entryContent.css("min-height", entryContent.height());
});

function applyFilter() {
    // First, get the tags that are now required.
    var checkboxes = jQuery(".fastfilter-checkbox");
    var requiredTags = [];
    for (var t = 0; t < checkboxes.length; t++) {
        var checkbox = jQuery(checkboxes[t]);

        if(checkbox.prop('checked')) {
            requiredTags.push(checkbox.val());
        }
    }

    // Make the ones that have all these tags fade in.
    if (requiredTags.length == 0) {
        show(jQuery(".fastfilter-post-item"));
        hide(jQuery(".fastfilter-no-results"));
    } else {
        var sel = "." + requiredTags.join(".");
        var toShow = jQuery(".fastfilter-post-item" + sel);

        // The "No results" one.
        if (toShow.length == 0) {
            show(jQuery(".fastfilter-no-results"));
        } else {
            hide(jQuery(".fastfilter-no-results"));
        }

        show(toShow);

        // Make the others fade out.
        hide(jQuery(".fastfilter-post-item:not(" + sel + ")"));
    }
}

function show(jQueryObj) {
    switch(show_and_hide) {
        case "slide":
            jQueryObj.slideDown(250);
            break;
        case "fade":
            jQueryObj.fadeIn(500);
            break;
        case "invisible":
            jQueryObj.animate({
                opacity: 1
            }, 500);
            break;
    }
    
}

function hide(jQueryObj) {
    switch(show_and_hide) {
        case "slide":
            jQueryObj.slideUp(250);
            break;
        case "fade":
            jQueryObj.fadeOut(500);
            break;
        case "invisible":
            jQueryObj.animate({
                opacity: 0
            }, 500);
            break;
    }
}