/*
 REDAXO JavaScript library
 */

// -------------------------------------------------------------------------------------------------------------------

function getObj(name)
{
    if (document.getElementById)
    {
        this.obj = document.getElementById(name);
        if(this.obj)
            this.style = this.obj.style;
    }
    else if (document.all)
    {
        this.obj = document.all[name];
        if(this.obj)
            this.style = this.obj.style;
    }
    else if (document.layers)
    {
        this.obj = document.layers[name];
        if(this.obj)
            this.style = this.obj;
    }
}

function getObjArray(name)
{
    return document.getElementsByName(name);
}

// -------------------------------------------------------------------------------------------------------------------

function changeImage(id,img)
{
    if(document.getElementById(id)) {
        document.getElementById(id).src = img;
    }

}

// -------------------------------------------------------------------------------------------------------------------

var pageloaded = false;

function init()
{
    pageloaded = true;
}

// -------------------------------------------------------------------------------------------------------------------

function makeWinObj(name,url,posx,posy,width,height,extra)
{
    if (extra == 'toolbar') extra = 'scrollbars=yes,toolbar=yes';
    else if (extra == 'empty') extra = 'scrollbars=no,toolbar=no';
    else extra = 'scrollbars=yes,toolbar=no' + extra;

    this.name=name;
    this.url=url;
    this.obj=window.open(url,name,'left='+posx+',top='+posy+',width='+width+',height='+height+', ' + extra);

    // alert("x: "+posx+" | posy: "+posy);

    // this.obj.moveTo(posx,posy);
    this.obj.focus();

    return this;
}

function closeAll()
{
    for( var i=0;i<=winObjCounter;i++)
    {
        if(winObj[i]) winObj[i].obj.close();
    }
}

function newWindow(name,link,width,height,type)
{
    if (width==0) width=550;
    if (height==0) height=400;

    if (type == 'scrollbars')
    {
        extra = 'toolbar';
    }else if (type == 'empty')
    {
        extra = 'empty';
    }else
    {
        extra = type;
    }

    if (type=="nav")
    {
        posx = parseInt(screen.width/2)-390;
        posy = parseInt(screen.height/2) - 24 - 290;
        width= 320;
        height=580;
    }else if (type=="content")
    {
        posx = parseInt(screen.width/2) - 390 + 330;
        posy = parseInt(screen.height/2) - 24 - 290;
        width= 470;
        height=580;
    }else
    {
        posx = parseInt((screen.width-width)/2);
        posy = parseInt((screen.height-height)/2) - 24;
    }

    winObjCounter++;
    winObj[winObjCounter] = new makeWinObj(name,link,posx,posy,width,height,extra);

    if (rex.popupEvents && rex.popupEvents[name]) {
        rex.popupEvents[name] = {};
    }

    return winObj[winObjCounter].obj;
}

var winObj = new Array();
if (opener != null)
{
    try{
        if (typeof(opener.winObjCounter) == "number")
        {
            var winObjCounter = opener.winObjCounter;
        }
    } catch(e) {
        // in x-origin cases opener.winObjCounter would not be readable
        var winObjCounter = -1;
    }
}else
{
    var winObjCounter = -1;
}

function rex_retain_popup_event_handlers(eventName) {
    if (!opener || !opener.rex || !window.name) {
        return;
    }

    var events = opener.rex.popupEvents || {};

    if (events[window.name] && events[window.name][eventName]) {
        $.each(events[window.name][eventName], function (i, event) {
            opener.jQuery(window).on(eventName, event);
        });

        return;
    }

    var events = opener.jQuery._data(window, 'events');

    if (!events || !events[eventName]) {
        return;
    }

    var handlers = [];
    $.each(events[eventName], function (i, event) {
        handlers.push(event.handler);
    });

    if (!opener.rex.popupEvents) {
        opener.rex.popupEvents = {};
    }
    if (!opener.rex.popupEvents[window.name]) {
        opener.rex.popupEvents[window.name] = {};
    }

    opener.rex.popupEvents[window.name][eventName] = handlers;
}

function setValue(id,value)
{
    var field = new getObj(id);
    field.obj.value = value;
}

function deleteREX(id, i_list, i_select)
{
    var medialist = i_select+id;
    var needle = new getObj(medialist);
    var source = needle.obj;
    var sourcelength = source.options.length;
    var position = null;

    for (ii = 0; ii < sourcelength; ii++) {
        if (source.options[ii].selected) {
            position = ii;
            break;
        }
    }

    if(position != null)
    {
        source.options[position] = null;
        sourcelength--;

        // Wenn das erste gel�scht wurde
        if(position == 0)
        {
            // Und es gibt noch weitere,
            // -> selektiere das "neue" erste
            if(sourcelength > 0)
                source.options[0].selected = "selected";
        }
        else
        {
            // -> selektiere das neue an der stelle >position<
            if(sourcelength > position)
                source.options[position].selected= "selected";
            else
                source.options[position-1].selected= "selected";
        }
        writeREX(id, i_list, i_select);
    }
}

function moveREX(id, i_list, i_select, direction)
{
    var medialist = i_select+id;
    var needle = new getObj(medialist);
    var source = needle.obj;
    var sourcelength = source.options.length;
    var elements = new Array();
    var was_selected = new Array();

    for (ii = 0; ii < sourcelength; ii++) {

        elements[ii] = new Array();
        elements[ii]['value'] = source.options[ii].value;
        elements[ii]['title'] = source.options[ii].text;
        was_selected[ii] = false;

    }

    var inserted = 0;
    var was_moved = new Array();
    was_moved[-1] = true;
    was_moved[sourcelength] = true;

    if (direction == 'top') {
        for (ii = 0; ii < sourcelength; ii++) {
            if (source.options[ii].selected) {
                elements = moveItem(elements, ii, inserted);
                was_selected[inserted] = true;
                inserted++;
            }
        }
    }

    if (direction == 'up') {
        for (ii = 0; ii < sourcelength; ii++) {
            was_moved[ii] = false;
            if (source.options[ii].selected) {
                to = ii-1;
                if (was_moved[to]) {
                    to = ii;
                }
                elements = moveItem(elements, ii, to);
                was_selected[to] = true;
                was_moved[to] = true;
            }
        }
    }

    if (direction == 'down') {
        for (ii = sourcelength-1; ii >= 0; ii--) {
            was_moved[ii] = false;
            if (source.options[ii].selected) {
                to = ii+1;
                if (was_moved[to]) {
                    to = ii;
                }
                elements = moveItem(elements, ii, to);
                was_selected[to] = true;
                was_moved[to] = true;
            }
        }
    }

    if (direction == 'bottom') {
        inserted = 0;
        for (ii = sourcelength-1; ii >= 0; ii--) {
            if (source.options[ii].selected) {
                to = sourcelength - inserted-1;
                if (to > sourcelength) {
                    to = sourcelength;
                }

                elements = moveItem(elements, ii, to);
                was_selected[to] = true;
                inserted++;
            }
        }
    }

    for (ii = 0; ii < sourcelength; ii++) {
        source.options[ii] = new Option(elements[ii]['title'], elements[ii]['value']);
        source.options[ii].selected = was_selected[ii];
    }
    writeREX(id, i_list, i_select);
}

function writeREX(id, i_list, i_select)
{

    var v_list = i_list+id;
    var v_select = i_select+id;
    var source = document.getElementById(v_select);
    var sourcelength = source.options.length;
    var target = document.getElementById(v_list);

    target.value = "";

    for (i=0; i < sourcelength; i++) {
        target.value += (source[i].value);
        if (sourcelength > (i+1))  target.value += ',';
    }
}

function moveItem(arr, from, to)
{
    if (from == to || to < 0)
    {
        return arr;
    }

    tmp = arr[from];
    if (from > to)
    {
        for (index = from; index > to; index--) {
            arr[index] = arr[index-1];
        }
    } else {
        for (index = from; index < to; index++) {
            arr[index] = arr[index+1];
        }
    }
    arr[to] = tmp;
    return arr;
}

// Checkbox mit der ID <id> anhaken
function checkInput(id)
{
    if(id)
    {
        var result = new getObj(id);
        var input = result.obj;
        if(input != null)
        {
            input.checked = 'checked';
        }
    }
}

// Inputfield (Checkbox/Radio) mit der ID <id> Haken entfernen
function uncheckInput(id)
{
    if(id)
    {
        var result = new getObj(id);
        var input = result.obj;
        if(input != null)
        {
            input.checked = '';
        }
    }
}

// Wenn der 2. Parameter angegeben wird, wird die style.display Eigenschaft auf den entsprechenden wert gesetzt,
// Sonst wird der wert getoggled
function toggleElement(id,display)
{
    var needle;

    if(typeof(id) != 'object')
    {
        needle = new getObj(id);
    }
    else
    {
        needle = id;
    }

    if (typeof(display) == 'undefined')
    {
        display = needle.style.display == '' ? 'none' : '';
    }

    needle.style.display = display;
    return display;
}

// cookie functions

function setCookie(name, value, expires, path, domain, secure, samesite) {
    if (typeof expires != undefined && expires == "never") {
        // never expire means expires in 3000 days
        expires = new Date();
        expires.setTime(expires.getTime() + (1000 * 60 * 60 * 24 * 3000));
        expires = expires.toGMTString();
    }

    document.cookie = name + "=" + escape(value)
        + ((expires) ? "; expires=" + expires : "")
        + ((path) ? "; path=" + path : "")
        + ((domain) ? "; domain=" + domain : "")
        + ((secure) ? "; secure" : "")
        + ((samesite) ? "; samesite=" + samesite : "");
}

function getCookie(cookieName) {
    var theCookie = "" + document.cookie;
    var ind = theCookie.indexOf(cookieName);
    if (ind == -1 || cookieName == "")
        return "";

    var ind1 = theCookie.indexOf(';', ind);
    if (ind1 == -1)
        ind1 = theCookie.length;

    return unescape(theCookie.substring(ind + cookieName.length + 1, ind1));
}

// -------------------------------------------------------------------------------

// add eye-toggle to each password input
$(document).on('rex:ready', function (event, viewRoot) {
    $(viewRoot).find('input[type="password"]').each(function() {
        var $el = $(this);
        var $eye = jQuery('<i class="rex-icon rex-icon-view" aria-hidden="true"></i>');

        if ($el.parent("div.input-group").length == 0) {
            $el.wrap('<div class="input-group"></div>');
        }

        // insert into DOM first, as wrap() only works on DOM attached nodes.
        $el.after($eye);
        $eye
            .wrap('<span class="input-group-btn"></span>')
            .wrap('<button type="button" class="btn btn-view" tabindex="-1"></button>');

        $el.next('span.input-group-btn').find('button.btn').click(function(event) {
            $eye.toggleClass("rex-icon-view rex-icon-hide");

            if ($el.attr("type") === "password") {
                $el.attr("type", "text");
            } else {
                $el.attr("type", "password");
            }
            event.stopPropagation();
            event.preventDefault();
        });
    });
});

// -------------------------------------------------------------------------------

var onDocumentReady = function () {

    // ------------------ Accesskey Navigation
    $(document).keypress(function(event) {
        // return true if !rex.accesskeys or key is not 0-9 or a-z
        // keycodes: 48 => '0', 57 => '9', 97 => 'a', 122 => 'z'
        if (!rex.accesskeys || event.which < 48 || (event.which > 57 && event.which < 97) || event.which > 122) {
            return true;
        }

        var key = String.fromCharCode(event.which);
        var haystack = $("input[accesskey='"+ key +"'], button[accesskey='"+ key +"']");

        if(haystack.length > 0)
        {
            $(haystack.get(0)).click();
            return false;
        }
        else
        {
            haystack = $("a[accesskey='"+ key +"']");

            if(haystack.length > 0)
            {
                var hit = $(haystack.get(0));
                if(hit.attr("onclick") != undefined)
                    hit.click();
                else if(hit.attr("href") != undefined && hit.attr("href") != "#")
                    document.location = hit.attr("href");

                return false;
            }
        }
    });

    var laststate;
    $("body")
        .on("focus", "input,button,textarea,select,option,[contenteditable=true]", function(event) {
            laststate = rex.accesskeys;
            rex.accesskeys = false;
        })
        .on("blur", "input,button,textarea,select,option,[contenteditable=true]", function(event) {
            rex.accesskeys = laststate;
        });
    $("[autofocus]").trigger("focus");

    if ($('#rex-page-setup, #rex-page-login').length == 0 && getCookie('rex_htaccess_check') == '')
    {
        time = new Date();
        time.setTime(time.getTime() + 1000 * 60 * 60 * 24);
        setCookie('rex_htaccess_check', '1', time.toGMTString(), '', '', false, 'lax');
        checkHtaccess('bin', 'console');
        checkHtaccess('cache', '.redaxo');
        checkHtaccess('data', '.redaxo');
        checkHtaccess('src', 'core/boot.php');
    }

    function checkHtaccess(dir, file)
    {
        $.get(dir +'/'+ file +'?redaxo-security-self-test',
            function(data) {
                $('#rex-js-page-main').prepend('<div class="alert alert-danger" style="margin-top: 20px;">The folder <code>redaxo/'+ dir +'</code> is insecure. Please protect this folder.</div>');
                setCookie('rex_htaccess_check', '');
            }
        );
    }

    if (!("autofocus" in document.createElement("input"))) {
        $(".rex-js-autofocus").focus();
    }

    /*
     * Replace all SVG images with inline SVG
     */
    $('img.rex-js-svg').each(function(){
        var $img = jQuery(this);
        var imgID = $img.attr('id');
        var imgClass = $img.attr('class');
        var imgURL = $img.attr('src');

        jQuery.get(imgURL, function(data) {
            // Get the SVG tag, ignore the rest
            var $svg = jQuery(data).find('svg');

            // Add replaced image's ID to the new SVG
            if(typeof imgID !== 'undefined') {
                $svg = $svg.attr('id', imgID);
            }
            // Add replaced image's classes to the new SVG
            if(typeof imgClass !== 'undefined') {
                $svg = $svg.attr('class', imgClass+' replaced-svg');
            }

            // Remove any invalid XML tags as per http://validator.w3.org
            $svg = $svg.removeAttr('xmlns:a');

            // Replace image with new SVG
            $img.replaceWith($svg);

        }, 'xml');

    });

    $(document).on('show.bs.dropdown', '.dropdown', function () {
        var windowHeight = $(window).height();
        var rect = this.getBoundingClientRect();
        if (rect.top > windowHeight) return;
        var menuHeight = $(this).children('.dropdown-menu').height();
        $(this).toggleClass('dropup',
            ((windowHeight - rect.bottom) < menuHeight) &&
            (rect.top > menuHeight));
    });

    // keep session alive
    if ('login' !== rex.page && rex.session_keep_alive) {
        var keepAliveInterval = setInterval(function () {
            jQuery.ajax('index.php?page=credits', {
                cache: false
            });
        }, 5 * 60 * 1000 /* make ajax request every 5 minutes */);
        setTimeout(function () {
            clearInterval(keepAliveInterval);
        }, rex.session_keep_alive * 1000 /* stop request after x seconds - see config.yml */);
    }

    $('body').trigger('rex:ready', [$('body')]);
}

// -------------------------------------------------------------------------------

var pjax;
var pjaxDefaultConfig = {
    cacheBust: false,
    history: true,
    scrollTo: false,
    timeout: 10000,
    debug: false
}
var rexAjaxLoaderId;

// DOMContentLoaded
jQuery(document).ready(function ($) {

    // init
    onDocumentReady();

    // pjax
    // https://github.com/MoOx/pjax
    pjax = new Pjax({
        elements: [
            // elements within pjax containers, where elements are not disabled for pjax
            '[data-pjax-container] a:not([data-pjax=\'false\'])',
            '[data-pjax-container] form:not([data-pjax=\'false\'])',
            // elements with data-pax attribute, where value is not false
            'a[data-pjax]:not([data-pjax=\'false\'])',
            'form[data-pjax]:not([data-pjax=\'false\'])',
        ],
        selectors: [
            'title', // title
            '#rex-js-nav-top', // top navigation
            '#rex-js-nav-main', // main navigation
            '#rex-js-page-main', // page content
            '#rex-js-global-footer', // footer
        ],
        switches: {
            // navigation: switch inner HTML only to keep scroll position
            '#rex-js-nav-main': Pjax.switches.innerHTML,
            // page content: switch either given section or whole content
            '#rex-js-page-main': function(oldEl, newEl, options) {
                var source = oldEl;
                var target = newEl;
                // prepare switching a section only
                if (options.pjaxContainerSelector) {
                    var targetSection = newEl.querySelector(options.pjaxContainerSelector);
                    if (targetSection) {
                        source = oldEl.querySelector(options.pjaxContainerSelector);
                        target = targetSection;
                    }
                }
                // switch
                source.replaceWith(target);
                this.onSwitch();
            },
        },
        cacheBust: pjaxDefaultConfig.cacheBust,
        history: pjaxDefaultConfig.history,
        scrollTo: pjaxDefaultConfig.scrollTo,
        timeout: pjaxDefaultConfig.timeout,
        debug: pjaxDefaultConfig.debug,
    });

    document.addEventListener('pjax:send', function (e) {
        // show loader
        // show only if page takes longer than 200 ms to load
        window.clearTimeout(rexAjaxLoaderId);
        rexAjaxLoaderId = setTimeout(function () {
            document.querySelector('#rex-js-ajax-loader').classList.add('rex-visible');
        }, 200);
    });

    document.addEventListener('pjax:complete', function () {
        // reset pjax config to defaults
        pjax.options.cacheBust = pjaxDefaultConfig.cacheBust;
        pjax.options.history = pjaxDefaultConfig.history;
        pjax.options.scrollTo = pjaxDefaultConfig.scrollTo;
        pjax.options.timeout = pjaxDefaultConfig.timeout;
        pjax.options.debug = pjaxDefaultConfig.debug;
        // hide loader
        window.clearTimeout(rexAjaxLoaderId);
        document.querySelector('#rex-js-ajax-loader').classList.remove('rex-visible');
    });

    document.addEventListener('pjax:success', function () {
        // init page content
        onDocumentReady();
    });

    document.addEventListener('pjax:error', function (event) {
        switch (event.request.status) {
            case 401:
                // user not authorized -> redirect to login page
                window.location = 'index.php?page=login';
                break;
            default:
                // load URL and show error/whoops
                window.location = event.request.responseURL;
                break;
        }
    });

    document.addEventListener('click', handleClickAndSubmitEvents, true);
    document.addEventListener('submit', handleClickAndSubmitEvents, true);
});

// -------------------------------------------------------------------------------

// handle click and submit events
var handleClickAndSubmitEvents = function (event) {

    // handle confirm dialogs
    if (event.target.dataset.confirm) {
        if (!confirm(event.target.dataset.confirm)) {
            event.stopImmediatePropagation();
            event.preventDefault();
        }
    }

    // handle submit buttons
    if (event.type === 'click' && event.target.matches('[type=\'submit\']')) {
        event.target.setAttribute('data-clicked', 1);
    }

    // handle pjax links
    if (event.target.matches('[data-pjax-state]')) {
        var wrapper;

        // save value of pjax container (in order to handle content switch)
        wrapper = event.target.closest('[data-pjax-container]');
        if (wrapper) {
            pjax.options.pjaxContainerSelector = wrapper.dataset.pjaxContainer;
        }

        // handle links with pjax disabled
        wrapper = event.target.closest('[data-pjax-container]');
        if (wrapper && wrapper.dataset.pjaxContainer === 'false') {
            event.stopPropagation();
        }

        // handle (no) history via data attribute
        wrapper = event.target.closest('[data-pjax-no-history]');
        if (wrapper && wrapper.dataset.pjaxNoHistory === '1') {
            pjax.options.history = false;
        }

        // handle scrollTo via data attribute
        wrapper = event.target.closest('[data-pjax-scroll-to]');
        if (wrapper) {
            pjax.options.scrollTo = wrapper.dataset.pjaxScrollTo;
        }

        // handle scrollTo inside of forms
        if (event.target.tagName === 'FORM') {
            pjax.options.scrollTo = 0;
        }

        // handle form submit
        if (event.type === 'submit' && event.target.tagName === 'FORM') {
            var submitButton = event.target.querySelector('[data-clicked]');
            if (submitButton) {
                var hiddenField = document.createElement("input");
                hiddenField.setAttribute("type", "hidden");
                hiddenField.setAttribute("name", submitButton.getAttribute('name'));
                hiddenField.setAttribute("value", submitButton.dataset.clicked);
                event.target.appendChild(hiddenField);
            }
        }
    }
}

// handle key events
var handleKeyEvents = function (event) {

    // submit forms via strg/cmd + enter
    if (event.metaKey && event.keyCode === 13) {
        var form = event.target.closest('form');
        if (form) {
            // click apply button if available (e.g. when editing content)
            var applyButton = form.querySelector('.btn-apply');
            if (applyButton) {
                applyButton.click();
            } else {
                // click (first) submit button
                var submitButton = form.querySelector('[type=\'submit\']');
                if (submitButton) {
                    submitButton.click();
                }
            }
        }
    }
}
