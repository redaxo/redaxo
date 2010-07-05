
function selectMedia(filename, alt)
{
	var win = tinyMCEPopup.getWindowArg("window");
	var type = tinyMCEPopup.getWindowArg("typeid");
	win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = 'files/' + filename;
	if (alt) {
		if (win.document.getElementById("alt")) {
			win.document.getElementById("alt").value = alt;
		}	
		if (win.document.getElementById("title")) {
			win.document.getElementById("title").value = alt;
		}	
	}	
	if (type == 'image') {
		if (win.ImageDialog.getImageData) win.ImageDialog.getImageData();
		if (win.ImageDialog.showPreviewImage) win.ImageDialog.showPreviewImage('files/' + filename);
	}
	if (type == 'media' && win.generatePreview) {
		win.generatePreview(filename);
	}
	tinyMCEPopup.close();
}
