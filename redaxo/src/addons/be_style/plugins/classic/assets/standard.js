/* 
 REDAXO JavaScript library
 @package redaxo4 
 @version svn:$Id$
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
        this.obj=window.open(url,name,'width='+width+',height='+height+', ' + extra);

        // alert("x: "+posx+" | posy: "+posy);

        this.obj.moveTo(posx,posy);
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
                extra = type
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
if (opener != null)
{
  if (typeof(opener.winObjCounter) == "number")
  {
    var winObjCounter = opener.winObjCounter;
  }
}else
{
  var winObjCounter = -1;
}

// -------------------------------------------------------------------------------------------------------------------

function newPoolWindow(link) 
{
    newWindow( 'rexmediapopup'+(winObjCounter+1), link, 800,600,',status=yes,resizable=yes');
}

function newLinkMapWindow(link) 
{
    newWindow( 'linkmappopup', link, 800,600,',status=yes,resizable=yes');
}

function openMediaDetails(id, file_id, file_category_id)
{
  if (typeof(id) == 'undefined')
  {
    id = '';  
  }
  newPoolWindow('index.php?page=mediapool&subpage=detail&opener_input_field='+ id + '&file_id=' + file_id + '&file_category_id=' + file_category_id);
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
    param = param + '&subpage=detail&file_name='+ value;
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
  newPoolWindow('index.php?page=mediapool&action=media_upload&subpage=add_file&opener_input_field=REX_MEDIA_'+id+params);
}

function openLinkMap(id, param)
{
  if (typeof(id) == 'undefined')
  {
    id = '';  
  }
  if (typeof(param) == 'undefined')
  {
    param = '';  
  }
  newLinkMapWindow('index.php?page=linkmap&opener_input_field=' + id + param);
}

function setValue(id,value)
{
  var field = new getObj(id);
  field.obj.value = value;
}

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

function deleteREXLink(id)
{
  var link;
  link = new getObj("LINK_"+id);
  link.obj.value = "";
  link = new getObj("LINK_"+id+"_NAME");
  link.obj.value = "";
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
      param += '&subpage=detail&file_name='+ source.options[ii].value;
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
      param += '&subpage=detail&file_name='+ source.options[ii].value;
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
  newPoolWindow('index.php?page=mediapool&action=media_upload&subpage=add_file&opener_input_field=REX_MEDIALIST_'+id+params);
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

function openREXLinklist(id, param)
{
  var linklist = 'REX_LINKLIST_'+id;
  var linkselect = 'REX_LINKLIST_SELECT_'+id;
  var needle = new getObj(linkselect);
  var source = needle.obj;
  var sourcelength = source.options.length;
  
  if ( typeof(param) == 'undefined')
  {
    param = '';  
  }

  for (ii = 0; ii < sourcelength; ii++) {
    if (source.options[ii].selected) {
      param = '&action=link_details&file_name='+ source.options[ii].value;
      break;
    }
  }
  
  newLinkMapWindow('index.php?page=linkmap&opener_input_field='+linklist+param);
}

function deleteREXLinklist(id){
  deleteREX(id, 'REX_LINKLIST_', 'REX_LINKLIST_SELECT_');
}

function moveREXLinklist(id, direction){
  moveREX(id, 'REX_LINKLIST_', 'REX_LINKLIST_SELECT_', direction);
}

function writeREXLinklist(id){
  writeREX(id, 'REX_LINKLIST_', 'REX_LINKLIST_SELECT_');
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

    // Wenn das erste gelöscht wurde
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
      url = '../files/'+ value;
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
      // img tag nur einmalig einfügen, ggf erzeugen wenn nicht vorhanden
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
     var haystack = $("input[accesskey="+ key +"]");
     
     if(haystack.size() > 0)
     {
       $(haystack.get(0)).click();
       return false;
     }
     else
     {
       haystack = $("a[accesskey="+ key +"]");
       
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
  
  $(function() {
    $("input,button,textarea,select,option")
      .focus(function(event) {
        ENABLE_KEY_NAV = false;
      })
      .blur(function(event) {
        ENABLE_KEY_NAV = true;
      });    
  });

});