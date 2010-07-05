
function em_openRelation(id,subpage,fieldname)
{
  var value = document.getElementById("REX_RELATION_"+id).value;
  newPoolWindow('index.php?page=editme&subpage=' + subpage + '&rex_em_opener_field=' + id + '&rex_em_opener_fieldname=' + fieldname);
}

function em_deleteRelation(id,subpage,fieldname)
{
	document.getElementById("REX_RELATION_" + id).value = "";
	document.getElementById("REX_RELATION_TITLE_" + id).value = "";
}

function em_addRelation(id,subpage,fieldname)
{
  newPoolWindow('index.php?page=editme&subpage=' + subpage + '&func=add&rex_em_opener_field=' + id + '&rex_em_opener_fieldname=' + fieldname);
}

function em_setData(id,data_id,data_name)
{
  if ( typeof(data_name) == 'undefined')
  {
	  data_name = '';  
  }
  opener.document.getElementById("REX_RELATION_" + id).value = data_id;
  opener.document.getElementById("REX_RELATION_TITLE_" + id).value = data_name;
  self.close();
}
