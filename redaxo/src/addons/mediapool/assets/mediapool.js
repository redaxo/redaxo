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
    if (!countCheckBoxes) {
        objCheckBoxes.checked = CheckValue;
    } else {
        // set the check value for all check boxes
        for(var i = 0; i < countCheckBoxes; i++) {
            objCheckBoxes[i].checked = CheckValue;
        }
    }
}

function newPoolWindow(link)
{
    var counter = opener ? opener.winObjCounter + 1 : 0;
    // 1200 = $screen-lg
    return newWindow( 'rexmediapopup'+counter, link, 1200,800,',status=yes,resizable=yes');
}

function openMediaDetails(id, file_id, file_category_id)
{
    if (typeof(id) == 'undefined')
    {
        id = '';
    }
    return newPoolWindow('index.php?page=mediapool/media&opener_input_field='+ id + '&file_id=' + file_id + '&file_category_id=' + file_category_id);
}

function openMediaPool(id)
{
    if (typeof(id) == 'undefined')
    {
        id = '';
    }
    return newPoolWindow('index.php?page=mediapool/media&opener_input_field='+ id);
}

function openREXMedia(id,param)
{
    var mediaid = 'REX_MEDIA_'+id;
    if (typeof(param) == 'undefined')
    {
        param = '';
    }
    return newPoolWindow('index.php?page=mediapool/media' + param + '&opener_input_field=' + mediaid);
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
        param = param + '&file_name='+ value;
        return newPoolWindow('index.php?page=mediapool/media' + param + '&opener_input_field=' + mediaid);
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
    return newPoolWindow('index.php?page=mediapool/upload&opener_input_field=REX_MEDIA_'+id+params);
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
            param += '&file_name='+ source.options[ii].value;
            break;
        }
    }
    return newPoolWindow('index.php?page=mediapool/media'+ param +'&opener_input_field='+ medialist);
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
            param += '&file_name='+ source.options[ii].value;
            break;
        }
    }
    if(param != '')
        return newPoolWindow('index.php?page=mediapool/media' + param + '&opener_input_field=' + medialist);
}

function addREXMedialist(id,params)
{
    if (typeof(params) == 'undefined')
    {
        params = '';
    }
    return newPoolWindow('index.php?page=mediapool/upload&opener_input_field=REX_MEDIALIST_'+id+params);
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

function getPreviewUrl(media, mediaManager) {
    var url = '';
	if('.svg' != media.substr(media.length - 4) && mediaManager) {
		url = './index.php?rex_media_type=rex_mediabutton_preview&rex_media_file='+ media;
	}
	else {
		url = '../media/'+ media;
	}	
	return url;
}

function isMedia(media) {
	return media && media.length != 0 && $.inArray(media.split('.').pop(), rex.imageExtensions);
}

$(document).ready(function () {
    // ------------------ Preview fuer REX_MEDIA_BUTTONS, REX_MEDIALIST_BUTTONS
    $('.rex-js-widget-preview').each(function () {
	    
	    var $this = $(this);
        var mediaManager = $this.hasClass("rex-js-widget-preview-media-manager");
        var $triggers = [];
        
        if($this.hasClass("rex-js-widget-media")) {
	        $triggers.push({
		        media: $this.find("input[type=text]").val(),
		        el: $this,
		        on: 'hover focus'
	        });
        } else {
	        $this.find("select option").each(function(){
		        $triggers.push({
			        media: $(this).text(),
			        el: $(this),
			        on: 'hover'
		        });
	        });
        }
                
        $.each($triggers, function(){
	        
	        var entry = this;
	        
	        if(isMedia(entry.media)) {
		        
		        var url = getPreviewUrl(entry.media, mediaManager);
	            var img = "<img src='"+url+"' width='200' />";

		        entry.el.tooltip({
			        container: $this,
		            html: true,
		            trigger: entry.on,
		            title: img,
		            delay: { "show": 250, "hide": 0 },
		            template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner" style="padding:0"></div></div>'
		        });
		        
	        } else {
		        return;
	        }
	        
        });

    });

});
