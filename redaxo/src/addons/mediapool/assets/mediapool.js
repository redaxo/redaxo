/*
 REDAXO Mediapool JavaScript library
 */

function setAllCheckBoxes(FieldName, mthis)
{
  var CheckValue;

  if (mthis.checked) CheckValue=true;
  else CheckValue=false;

  var objCheckBoxes = new getObjArray(FieldName);
  if(!objCheckBoxes) return;

  var countCheckBoxes = objCheckBoxes.length;
  if(!countCheckBoxes) objCheckBoxes.checked = CheckValue;
  else
    // set the check value for all check boxes
    for(var i = 0; i < countCheckBoxes; i++)
      objCheckBoxes[i].checked = CheckValue;
}

function newPoolWindow(link)
{
    newWindow( 'rexmediapopup'+(winObjCounter+1), link, 800,600,',status=yes,resizable=yes');
}

function openMediaDetails(id, file_id, file_category_id)
{
  if (typeof(id) == 'undefined')
  {
    id = '';
  }
  newPoolWindow('index.php?page=mediapool/media&opener_input_field='+ id + '&file_id=' + file_id + '&file_category_id=' + file_category_id);
}

function openMediaPool(id)
{
  if (typeof(id) == 'undefined')
  {
    id = '';
  }
  newPoolWindow('index.php?page=mediapool&opener_input_field='+ id);
}

function openREXMedia(id,param)
{
  var mediaid = 'REX_MEDIA_'+id;
  if (typeof(param) == 'undefined')
  {
    param = '';
  }
  newPoolWindow('index.php?page=mediapool' + param + '&opener_input_field=' + mediaid);
}

function viewREXMedia(id,param)
{
  var mediaid = 'REX_MEDIA_'+id;
  var value = document.getElementById(mediaid).value;
  if ( typeof(param) == 'undefined')
  {
    param = '';
  }
  if (value != '') {
    param = param + '&subpage=media&file_name='+ value;
    newPoolWindow('index.php?page=mediapool' + param + '&opener_input_field=' + mediaid);
  }
}

function deleteREXMedia(id)
{
    var a = new getObj("REX_MEDIA_"+id);
    a.obj.value = "";
}

function addREXMedia(id,params)
{
  if (typeof(params) == 'undefined')
  {
    params = '';
  }
  newPoolWindow('index.php?page=mediapool/upload&opener_input_field=REX_MEDIA_'+id+params);
}

function openREXMedialist(id,param)
{
  var medialist = 'REX_MEDIALIST_' + id;
  var mediaselect = 'REX_MEDIALIST_SELECT_' + id;
  var needle = new getObj(mediaselect);
  var source = needle.obj;
  var sourcelength = source.options.length;
  if ( typeof(param) == 'undefined')
  {
    param = '';
  }
  for (ii = 0; ii < sourcelength; ii++) {
    if (source.options[ii].selected) {
      param += '&subpage=media&file_name='+ source.options[ii].value;
      break;
    }
  }
  newPoolWindow('index.php?page=mediapool'+ param +'&opener_input_field='+ medialist);
}

function viewREXMedialist(id,param)
{
  var medialist = 'REX_MEDIALIST_' + id;
  var mediaselect = 'REX_MEDIALIST_SELECT_' + id;
  var needle = new getObj(mediaselect);
  var source = needle.obj;
  var sourcelength = source.options.length;
  if ( typeof(param) == 'undefined')
  {
    param = '';
  }
  for (ii = 0; ii < sourcelength; ii++) {
    if (source.options[ii].selected) {
      param += '&subpage=media&file_name='+ source.options[ii].value;
      break;
    }
  }
  if(param != '')
    newPoolWindow('index.php?page=mediapool' + param + '&opener_input_field=' + medialist);
}

function addREXMedialist(id,params)
{
  if (typeof(params) == 'undefined')
  {
    params = '';
  }
  newPoolWindow('index.php?page=mediapool/upload&opener_input_field=REX_MEDIALIST_'+id+params);
}

function deleteREXMedialist(id){
  deleteREX(id, 'REX_MEDIALIST_', 'REX_MEDIALIST_SELECT_');
}

function moveREXMedialist(id, direction){
  moveREX(id, 'REX_MEDIALIST_', 'REX_MEDIALIST_SELECT_', direction);
}

function writeREXMedialist(id){
  writeREX(id, 'REX_MEDIALIST_', 'REX_MEDIALIST_SELECT_');
}


/*
 * jQuery Images Ondemand v0.2
 *
 * This script load images on scroll event.
 * You only have to add 'img-ondemand' class and chage put src content on longdesc propertie on img tags
 * ej:
 *    original img    <img src='my_pic.jpg' />
 *    new img         <img class='img-ondemand' longdesc='my_pic.jpg' src='' />
 *
 * Copyright (c) 2009 Martin Borthiry : martin.borthiry@gmail.com
 * Dual licensed under the MIT and GPL licenses.
 *
 * Date: 2009-09-21
 * Revision: 2
 */

(function($){

    // global variables
    var $w = $(window);
    var imgToLoad = 1;
    var className = 'img-ondemand';
    // offset of bottom to load images, on px
    var offset = 50;


    function imgOndemand(){
        if (imgToLoad){
             //calc current scroll position
            var scrollPos = $w.height() +$w.scrollTop();

            // get imgs not loaded
            $('img.'+className).each(function(){
                var $img = $(this);
                // filter imgs over scroll limit
                if($img.offset().top < scrollPos+offset){
                    $img.attr('src',$img.attr('longdesc')).removeClass(className);
                }
            });
            // flag on bottom
            imgToLoad = $('img.'+className).length;

        }

    }

    // load on start all imgs over scroll limit
    $(function(){
        imgOndemand();
    });

    //bind scroll event (if you need you can add window resize event)
    $w.scroll(function(){
        imgOndemand();
    });


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
      if($(this).hasClass("rex-widget-preview-media-manager"))
        url = '../index.php?rex_media_type='+ img_type +'&rex_media_file='+ value;
      else
      {
        url = '../media/'+ value;
        width = 246;
      }

      if(value && value.length != 0 && $.inArray(value.split('.').pop(), rex.imageExtensions))
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


})(jQuery);
