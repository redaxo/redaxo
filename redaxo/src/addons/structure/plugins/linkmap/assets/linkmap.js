/*
 REDAXO Linkmap JavaScript library
 */
function newLinkMapWindow(link)
{
  newWindow( 'linkmappopup', link, 800,600,',status=yes,resizable=yes');
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

function deleteREXLink(id)
{
  var link;
  link = new getObj("LINK_"+id);
  link.obj.value = "";
  link = new getObj("LINK_"+id+"_NAME");
  link.obj.value = "";
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

  for (var ii = 0; ii < sourcelength; ii++) {
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

// ----------------- sitemap functions

jQuery(function($){
  // insert empty child list, so drag&drop works in every level (not only where we already childs exists)
  $('#rex-sitemap li:not(:contains(ul))').each(function (){
    $('<ul cat-id="'+ $(this).attr('parent-id') +'" />').appendTo(this);
  });

  var triggered = false;
  // see http://a.shinynew.me/post/4641524290/jquery-ui-nested-sortables
  $('#rex-sitemap ul').sortable({
    connectWith: '#rex-sitemap ul',
    placeholder: 'rex-tree-highlight',
    update: function(event, ui) {
      // prevent double invocation
      if(triggered) {
        return;
      }
      triggered = true;

      var dragedItem = ui.item;
      var draggedId = dragedItem.attr('cat-id');
      var newCatId = dragedItem.closest('ul').attr('cat-id');

      // update the prior to bring the cat into position
      var prevPrior = dragedItem.prev('li').attr('prior');
      var newPrior = prevPrior ? (parseInt(prevPrior, 10) + 1) : 0;

      $.ajax({
            async: false,
            dataType: 'json',
        url : 'index.php',
        data : {
          'rex-api-call' : 'category-move',
          'category-id' : draggedId,
          'new-category-id' : newCatId,
          'new-prior' : newPrior
        }
      });
    },
    start: function(event, ui) {
      triggered = false;
    }
  });
});
