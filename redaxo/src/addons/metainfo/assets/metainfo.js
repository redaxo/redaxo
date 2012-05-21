var visibleNotice;

function meta_checkConditionalFields(selectEl, activeIds, textIds) {
  var toggle = false;

  for ( var i = 0; i < activeIds.length; i++) {
    if (selectEl.value == activeIds[i]) {
      toggle = activeIds[i];
      break;
    }
  }

  if (toggle) {
    if (visibleNotice) {
      toggleElement(visibleNotice, 'none');
    }

    needle = new getObj('rex_metainfo_field_params_notice_' + toggle);
    if (needle.obj) {
      toggleElement(needle.obj, '');
      visibleNotice = needle.obj;
    }
  } else {
    if (visibleNotice) {
      toggleElement(visibleNotice, 'none');
    }
  }

  var show = 1;
  for ( var i = 0; i < textIds.length; i++) {
    if (selectEl.value == textIds[i]) {
      show = 0;
      break;
    }
  }

  jQuery(function($) {
    if (show == 1) {
      $("#rex_metainfo_params_Feld_bearbeiten_erstellen_default").parent().show();
    }else {
      $("#rex_metainfo_params_Feld_bearbeiten_erstellen_default").parent().hide();
    }
  });

};


jQuery( function($) {
  function disableSelect(chkbox) {
    var sibling = chkbox;
    while (sibling != null) {
      if (sibling.nodeType == 1 && sibling.tagName.toLowerCase() == "select") {
        $(sibling).prop('disabled', !chkbox.checked);
      }
      sibling = sibling.previousSibling;
    }
  }
  ;

  $("input[type=checkbox].rex-metainfo-checkbox").click( function() {
    disableSelect(this);
  });
  $("input[type=checkbox].rex-metainfo-checkbox").each( function() {
    disableSelect(this);
  });
});
