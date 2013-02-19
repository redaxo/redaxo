function componentInit(componentId) {
    if (getCookie(componentId + "_state") == "minimized") {
        jQuery("#" + componentId + " .rex-dashboard-component-content").hide();
        refreshComponentToggleItem(componentId);
    }
}

function componentRefresh(componentId) {
    var component = jQuery("#" + componentId);
    var link = jQuery("#"+ componentId + "-refresh");

    // prepare url
    var url = window.location.href;

    // search for the # in the url
    if(url.indexOf('#') >= 0)
    {
        // strip anchor from the url
        url = url.substr(0, url.indexOf('#'));
    }

    // add refresh parameter so we get a up-to-date copy
    url += '&refresh=' + componentId;
    // get content for ajax usage
    url += '&ajax-get=' + componentId;

    loadingFn = function() {
        // indicate loading with animated image
        link.removeClass("rex-i-refresh").removeClass("rex-i-refresh-err").addClass("rex-i-refresh-ani");
    };

    errorFn = function() {
        // indicate loading with animated image
        link.removeClass("rex-i-refresh").removeClass("rex-i-refresh-ani").addClass("rex-i-refresh-err");
    };

    successFn = function(data) {
        // stop indicator
        link.removeClass("rex-i-refresh-ani").removeClass("rex-i-refresh-err").addClass("rex-i-refresh");
        component.replaceWith(data);
    };

    jQuery.ajax( {
        'url' : url,
        beforeSend : loadingFn,
        error : errorFn,
        success : successFn
    });
}

function componentToggleSettings(componentId,newState) {
    var component = jQuery("#" + componentId);
    var config = jQuery(".rex-dashboard-component-config", component);
    var link = jQuery("#" + componentId + "-togglesettings");

    if(typeof newState != "undefined")
    {
        if(newState == "hide")
        {
            link.removeClass("rex-i-togglesettings-off").addClass("rex-i-togglesettings");
            config.hide("slow");
        }
        else
        {
            link.removeClass("rex-i-togglesettings").addClass("rex-i-togglesettings-off");
            config.show("slow");
        }
    }
    else
    {
        link.rexToggleClass("rex-i-togglesettings", "rex-i-togglesettings-off");
        config.slideToggle("slow");
    }
}

function componentToggleView(componentId) {
    var component = jQuery("#" + componentId);
    var content = jQuery(".rex-dashboard-component-content", component);
    var wasHidden = content.is(":hidden");

    if (!wasHidden) {
        componentToggleSettings(componentId, "hide");
    }
    content.slideToggle("slow");

    refreshComponentToggleItem(componentId);
    setCookie(componentId + "_state", (wasHidden ? "maximized" : "minimized"),
            "never");
}

function refreshComponentToggleItem(componentId) {
    var link = jQuery("#" + componentId + "-toggleview");
    link.rexToggleClass("rex-i-toggleview", "rex-i-toggleview-off");
}

jQuery.fn.rexToggleClass = function(class1, class2)
{
    if(this.hasClass(class1))
    {
        this.removeClass(class1).addClass(class2);
    }
    else
    {
        this.removeClass(class2).addClass(class1);
    }
}
