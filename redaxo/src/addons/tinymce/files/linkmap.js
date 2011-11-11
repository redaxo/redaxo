
function insertLink(link,name)
{
	var win = tinyMCEPopup.getWindowArg("window");
	win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = link;
	win.document.getElementById("title").value = name;
	tinyMCEPopup.close();
}
