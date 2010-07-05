/**
 * @author Andreas Eberhard
 * http://andreaseberhard.de - http://projekte.andreaseberhard.de/tinyredaxo
 */

var redaxoMediaDialog = {

	init : function(ed) {

		tinyMCEPopup.resizeToInnerSize();

		document.getElementById('srcbrowsercontainer').innerHTML = getBrowserHTML('srcfilebrowser','src','media','media');

		var formObj = document.forms[0];
		var inst = tinyMCEPopup.editor;
		var elm = inst.selection.getNode();
		var wsrc = '';
		var wtitle = '';
		var wid = '';
		var wclass = '';
		var wrel = '';
		var action = 'insert';

		elm = inst.dom.getParent(elm, 'A');
		if (elm != null && elm.nodeName == 'A')
			action = 'update';

		formObj.insert.value = tinyMCEPopup.getLang(action, 'Insert', true);

		if (action == 'update') {
			wsrc = inst.dom.getAttrib(elm, 'href');
			wtitle = inst.dom.getAttrib(elm, 'title');
			wnewwin = inst.dom.getAttrib(elm, 'onclick');
			if (wnewwin != '' ) {
				formObj.newwin.checked = true;
			}
			wid = inst.dom.getAttrib(elm, 'id');
			wclass = inst.dom.getAttrib(elm, 'class');
			wrel = inst.dom.getAttrib(elm, 'rel');
		}

		setFormValue('src', wsrc);
		setFormValue('title', wtitle);
		setFormValue('id', wid);
		setFormValue('class', wclass);
		setFormValue('rel', wrel);

	},

	update : function() {
	
		var formObj = document.forms[0];
		var inst = tinyMCEPopup.editor;
		var elm, elementArray, i;

		var elm = inst.selection.getNode();
		elm = inst.dom.getParent(elm, "A");

		tinyMCEPopup.execCommand("mceBeginUndoLevel");

		if (elm != null && elm.nodeName == 'A') {
			setAttrib(elm, 'href', getFormValue('src'));
			setAttrib(elm, 'title', getFormValue('title'));
			setAttrib(elm, 'id', getFormValue('id'));
			setAttrib(elm, 'class', getFormValue('class'));
			setAttrib(elm, 'rel', getFormValue('rel'));
			if (formObj.newwin.checked == true) {
				setAttrib(elm, 'onclick', 'window.open(this.href); return false;');
			} else {
				setAttrib(elm, 'onclick', '');
			}
		} else {
			tinyMCEPopup.execCommand("CreateLink", false, "#mce_temp_url#", {skip_undo : 1});
			elementArray = tinymce.grep(inst.dom.select("a"), function(n) {return inst.dom.getAttrib(n, 'href') == '#mce_temp_url#';});
			for (i=0; i<elementArray.length; i++) {
				elm = elementArray[i];
				setAttrib(elm, 'href', getFormValue('src'));
				setAttrib(elm, 'title', getFormValue('title'));
				setAttrib(elm, 'id', getFormValue('id'));
				setAttrib(elm, 'class', getFormValue('class'));
				setAttrib(elm, 'rel', getFormValue('rel'));
				if (formObj.newwin.checked == true) {
					setAttrib(elm, 'onclick', 'window.open(this.href); return false;');
				} else {
					setAttrib(elm, 'onclick', '');
				}
			}
		}

		// Don't move caret if selection was image
		if (elm.childNodes.length != 1 || elm.firstChild.nodeName != 'IMG') {
			inst.focus();
			inst.selection.select(elm);
			inst.selection.collapse(0);
			tinyMCEPopup.storeSelection();
		}

		tinyMCEPopup.execCommand("mceEndUndoLevel");
		tinyMCEPopup.close();
	}
};

function setFormValue(name, value) {
	document.forms[0].elements[name].value = value;
}
function getFormValue(name) {
	return document.forms[0].elements[name].value;
}

function setAttrib(elm, attrib, value) {
	var formObj = document.forms[0];
	var valueElm = formObj.elements[attrib.toLowerCase()];
	var dom = tinyMCEPopup.editor.dom;

	if (typeof(value) == "undefined" || value == null) {
		value = "";

		if (valueElm)
			value = valueElm.value;
	}

	// Clean up the style
	if (attrib == 'style')
		value = dom.serializeStyle(dom.parseStyle(value));

	dom.setAttrib(elm, attrib, value);
}

tinyMCEPopup.requireLangPack();
tinyMCEPopup.onInit.add(redaxoMediaDialog.init, redaxoMediaDialog);
