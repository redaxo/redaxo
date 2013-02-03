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
        winObj[winObjCounter]=new makeWinObj(name,link,posx,posy,width,height,extra);
}

var winObj = new Array();
if (typeof opender != "undefined")
{
  if (typeof(opener.winObjCounter) == "number")
  {
    var winObjCounter = opener.winObjCounter;
  }
}else
{
  var winObjCounter = -1;
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


jQuery(function($){
  // ------------------ Preview fuer REX_MEDIA_BUTTONS, REX_MEDIALIST_BUTTONS
  function rexShowMediaPreview() {
    var value, img_type;
    if($(this).hasClass("rex-widget-media"))
    {
      value = $("input[type=text]", this).val();
      img_type = "rex_mediabutton_preview";
    }else
    {
      value = $("select :selected", this).text();
      img_type = "rex_medialistbutton_preview";
    }

    var div = $(".rex-media-preview", this);

    var url;
    var width = 0;
    if($(this).hasClass("rex-widget-preview-image-manager"))
      url = '../index.php?rex_img_type='+ img_type +'&rex_img_file='+ value;
    else if($(this).hasClass("rex-widget-preview-image-resize"))
      url = '../index.php?rex_resize=246a__'+ value;
    else
    {
      url = '../media/'+ value;
      width = 246;
    }

    if(value && value.length != 0 &&
      (
        value.substr(-3) == "png" ||
        value.substr(-3) == "gif" ||
        value.substr(-3) == "bmp" ||
        value.substr(-3) == "jpg" ||
        value.substr(-4) == "jpeg")
      )
    {
      // img tag nur einmalig einf�gen, ggf erzeugen wenn nicht vorhanden
      var img = $('img', div);
      if(img.length == 0)
      {
          div.html('<img />');
          img = $('img', div);
      }
      img.attr('src', url);
      if (width != 0)
        img.attr('width', width);

      div.slideDown("fast");
    }
    else
    {
      div.slideUp("fast");
    }
  };

  // Medialist preview neu anzeigen, beim wechsel der auswahl
  $(".rex-widget-medialist.rex-widget-preview")
    .click(rexShowMediaPreview);

  $(".rex-widget-media.rex-widget-preview, .rex-widget-medialist.rex-widget-preview")
    .bind("mousemove", rexShowMediaPreview)
    .bind("mouseleave", function() {
      var div = $(".rex-media-preview", this);
      if(div.css('height') != 'auto')
      {
        div.slideUp("normal");
      }
  });

  // ------------------ Accesskey Navigation
  var ENABLE_KEY_NAV = true;

  $(document).keypress(function(event) {
    if(!ENABLE_KEY_NAV)
      return true;

     var key = String.fromCharCode(event.which);
     var haystack = $("input[accesskey='"+ key +"']");

     if(haystack.size() > 0)
     {
       $(haystack.get(0)).click();
       return false;
     }
     else
     {
       haystack = $("a[accesskey='"+ key +"']");

       if(haystack.size() > 0)
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

  $("body")
    .on("focus", "input,button,textarea,select,option", function(event) {
      ENABLE_KEY_NAV = false;
    })
    .on("blur", "input,button,textarea,select,option", function(event) {
      ENABLE_KEY_NAV = true;
    });

  if ($('#rex-page-login').length == 0 && getCookie('htaccess_check') == '')
  {
    time = new Date();
    time.setTime(time.getTime() + 1000 * 60 * 60 * 24);
    setCookie('htaccess_check', '1', time.toGMTString());
    checkHtaccess('cache', '.redaxo');
    checkHtaccess('data', 'config.yml');
    checkHtaccess('src', 'core/master.php');
  }

  function checkHtaccess(dir, file)
  {
    $.get(dir +'/'+ file,
    function(data) {
      $('#rex-wrapper2').prepend('<div class="rex-message"><p class="rex-warning"><span>The folder redaxo/'+ dir +' is insecure. Please protect this folder.</span></p></div>');
      setCookie('htaccess_check', '');
    }
  );
  }
});


// cookie functions
// necessary for be_dashboard

function setCookie(name, value, expires, path, domain, secure) {
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
      + ((secure) ? "; secure" : "");
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

jQuery(document).ready(function($) {


  confDialog = function(event) {
    if(!confirm($(this).attr('data-confirm')))
    {
      event.stopImmediatePropagation();
      return false;
    }
  };

  // confirm dialog behavior for links and buttons
  $(document).on('click', 'a[data-confirm], button[data-confirm], input[data-confirm]', confDialog);
  // confirm dialog behavior for forms
  $(document).on('submit', 'form[data-confirm]', confDialog);


  // prevent pjax from jumping to top, see github#60
  /*
  $.pjax.defaults.scrollTo = false;
  $.pjax.defaults.timeout = 5000;
  $.pjax.defaults.maxCacheLength = 0;

  // install pjax handlers, see defunkt/jquery-pjax#142
  $(document).on('click', '[data-pjax-container] a, a[data-pjax]', function(event) {
    var self = $(this), container;

    if(event.isDefaultPrevented())
    {
      return;
    }

    if(self.is('a[data-pjax]'))
    {
      container = self.attr('data-pjax');
    }
    else
    {
      container = self.closest('[data-pjax-container]').attr('data-pjax-container');
    }

    if (container !== 'false') {
      return $.pjax.click(event, container);
    }
  });

  // add pjax error handling
  $(document)
    .on('pjax:error', function(e, xhr, err) {
      // user not authorized -> redirect to login page
      if (xhr.status === 401)
      {
         window.location = 'index.php?page=login';
         return false;
      }
      $('#rex-message-container').text('Something went wrong: ' + err);
    })
    .on('pjax:success', function(e, data, status, xhr, options) {
      var
        paramUrl = options.url.split('?'),
        page,
        subpage = '';

      $.each(paramUrl[1].split('&'), function(_, value) {
        var parts = value.split('=');
        if(parts[0] == 'page')
          page = parts[1];
        else if(parts[0] == 'subpage')
          subpage = parts[1];
      });

      $('.rex-navi-main .rex-active').removeClass('rex-active');

      // activate main-page
      $('#rex-navi-page-' + page).addClass('rex-active');
      $('#rex-navi-page-' + page + ' > a').addClass('rex-active');
      // activate sub-page
      $('#rex-navi-page-' + page + ' li > a[href$=\'subpage='+ subpage +'\']').addClass('rex-active')
        .parent('li').addClass('rex-active');
    })
    .on('pjax:start', function() { $('#rex-ajax-loader').show(); })
    .on('pjax:end',   function() { $('#rex-ajax-loader').hide(); });

  */
});
