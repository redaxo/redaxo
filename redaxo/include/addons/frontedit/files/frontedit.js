/*
mozile.root = "mozile/";

// LOAD MODULES
// Load desired modules. They'll take care of their own requirements.
//mozile.require("mozile.dom");
//mozile.require("mozile.xml");
//mozile.require("mozile.xpath");
//mozile.require("mozile.util");
//mozile.require("mozile.edit");
//mozile.require("mozile.event");
//mozile.require("mozile.save");
//mozile.require("mozile.gui");
//mozile.require("mozile.gui.htmlToolbar");

// MODULE CONFIG
// Configure the loaded modules.
//mozile.useDesignMode = false;
//mozile.defaultNS = "http://www.w3.org/1999/xhtml";
//mozile.help = [mozile.root, "doc", "html", "index.html"].join(mozile.filesep);
//mozile.debug.alertLevel = "inform";
//mozile.debug.logLevel = "debug";
//mozile.alternateSpace = "\u00A0";

// SAVE CONFIG
// Configure the save system.
//mozile.save.target = document;
//mozile.save.format = null;
//mozile.save.warn = true;
mozile.save.method = mozile.save.post;
mozile.save.post.async = true;
mozile.save.post.uri = "redaxo/index.php?page=front_edit&subpage=save";
//mozile.save.post.user = null;
//mozile.save.post.password = null;
mozile.save.post.showResponse = false;

// COMMANDS
// Run commands to finish configuring Mozile.
//mozile.editDocument();
mozile.enableEditing(false);
*/
var oldel = null;
var old_article_id = null;
var old_slice_id = null;

var clickel = null;

front_end_init();

var front_edit_overlay_div = null;
var front_edit_toolbar_div = null;
var front_edit_moduleselect_div = null;
var front_edit_maintoolbar_div = null;

var front_edit_addslice = "before";
var front_edit_addslice_article = null;
var front_edit_addslice_slice = null;

var last_mouse_x = 0;
var last_mouse_y = 0;

var edit = false;

if(readCookie("front_edit") == "1")
	edit = true;
	
function front_edit_on()
{
	edit = true;
	createCookie("front_edit", "1");
}

function front_edit_off()
{
	edit = false;
	createCookie("front_edit", "0");
}

function front_edit_command(cmd, opts, src_el)
{
	switch(cmd)
	{
		case "slice":
			var rel = clickel.getAttribute("rel");
			rel = rel.replace(/,/g, "&");
			var vars = rel.split("&");
			var article_id, ctype, slice_id;
			for(var i = 0; i < vars.length; i++)
			{
				var pair = vars[i].split("=");
				if(pair[0] == "article_id")
					article_id = pair[1];
				else if(pair[0] == "ctype")
					ctype = pair[1];
				else if(pair[0] == "slice_id")
					slice_id = pair[1];
			}
			var geturl;
			
			switch(opts)
			{
				case "move_up":
					geturl = 'redaxo/index.php?page=front_edit&mode=edit&' + rel + '&function=moveup&redirect=1';
					break;
				case "move_down":
					geturl = 'redaxo/index.php?page=front_edit&mode=edit&' + rel + '&function=movedown&redirect=1';
					break;
				case "delete":
					if(confirm('Löschen ?'))
					{
						geturl = 'redaxo/index.php?page=front_edit&mode=edit&' + rel + '&function=delete&save=1&redirect=1';
					}
					break;
				case "add_before":
					front_edit_addslice = "before";
					front_edit_addslice_article = article_id;
					front_edit_addslice_slice = slice_id;
					document.getElementById("front_edit_moduleselect").style.left = (last_mouse_x - parseInt(front_edit_toolbar_div.style.left)) + "px";
					document.getElementById("front_edit_moduleselect").style.top = (last_mouse_y - parseInt(front_edit_toolbar_div.style.top)) + "px";
					document.getElementById("front_edit_moduleselect").style.display = "block";
					break;
				case "add_after":
					front_edit_addslice = "after";
					front_edit_addslice_article = article_id;
					front_edit_addslice_slice = slice_id;
					document.getElementById("front_edit_moduleselect").style.left = (last_mouse_x - parseInt(front_edit_toolbar_div.style.left)) + "px";
					document.getElementById("front_edit_moduleselect").style.top = (last_mouse_y - parseInt(front_edit_toolbar_div.style.top)) + "px";
					document.getElementById("front_edit_moduleselect").style.display = "block";
					break;
				default:
					break;
			}
			
			if(geturl)
			{
				loadAndWait(geturl);
				reloadCType(article_id, ctype);
			}
			break;
		default:
			break;
	}
	
}

function addSlice(module_id)
{
	openWin('redaxo/index.php?page=front_edit&article_id='+front_edit_addslice_article+'&mode=edit&slice_id=0&function=add&'+front_edit_addslice+'slice='+front_edit_addslice_slice+'&module_id='+module_id);
}

function front_end_init()
{
	if(document.getElementsByTagName("body")[0])
	{
		front_edit_overlay_div = document.createElement("div");
		front_edit_overlay_div.className = "front_edit_overlay_div";
		document.getElementsByTagName("body")[0].appendChild(front_edit_overlay_div);
		
		front_edit_overlay_div.onclick = doclick;
		
		front_edit_toolbar_div = document.createElement("div");
		front_edit_toolbar_div.id = "front_edit_toolbar";
		
		front_edit_maintoolbar_div = document.createElement("div");
		front_edit_maintoolbar_div.id = "front_edit_maintoolbar";
		
		front_edit_maintoolbar_div.innerHTML += '<b>Front Edit</b>';
		front_edit_maintoolbar_div.innerHTML += '<a href="javascript:front_edit_on();">ein</a>';
		front_edit_maintoolbar_div.innerHTML += '<a href="javascript:front_edit_off();">aus</a>';
		document.getElementsByTagName("body")[0].appendChild(front_edit_maintoolbar_div);
		
		//XXX toolbar in eigene datei und reinladen...
		front_edit_toolbar_div.innerHTML += '<a href="javascript:front_edit_command(\'slice\', \'delete\');"><img src="redaxo/media/file_del.gif" border="0"></a>';
		front_edit_toolbar_div.innerHTML += '<a href="javascript:front_edit_command(\'slice\', \'move_up\');"><img src="redaxo/media/file_up.gif" border="0"></a>';
		front_edit_toolbar_div.innerHTML += '<a href="javascript:front_edit_command(\'slice\', \'move_down\');"><img src="redaxo/media/file_down.gif" border="0"></a>';
		front_edit_toolbar_div.innerHTML += '<br /><br />davor <a href="javascript:front_edit_command(\'slice\', \'add_before\', this);"><img src="redaxo/media/file_add.gif" border="0"></a>';
		front_edit_toolbar_div.innerHTML += '<br />danach <a href="javascript:front_edit_command(\'slice\', \'add_after\', this);"><img src="redaxo/media/file_add.gif" border="0"></a>';
		front_edit_toolbar_div.innerHTML += '<div id="front_edit_moduleselect"></div>';
		
		document.getElementsByTagName("body")[0].appendChild(front_edit_toolbar_div);
		
		document.onmousemove = function(ev)
		{
			if(edit)
				front_edit_mousemove(ev);
		}
		
		front_edit_moduleselect_div = document.getElementById("front_edit_moduleselect");
		loadIntoDiv(front_edit_moduleselect_div, "redaxo/index.php?page=front_edit&subpage=modulelist");
	}
	else
	{
		window.setTimeout("front_end_init();", 200);
	}
}

function front_edit_mousemove(ev)
{	
	var el;
	
	if(!ev)
	{
		ev = window.event;
		el = ev.srcElement;
		last_mouse_x = ev.screenX;
		last_mouse_y = ev.screenX;
	}
	else
	{
		el = ev.target;
		last_mouse_x = ev.pageX;
		last_mouse_y = ev.pageY;
	}
	
	if(el != front_edit_overlay_div && !partOf(el, front_edit_toolbar_div))
	{
		var lastel = null;
		
		while(el && el.parentNode)
		{
			if(el && el.className == "front_edit_block")
				lastel = el;
			el = el.parentNode;
		}
		
		el = lastel;
		
		/*
		if(el && el.className == "front_end_edit_text")
		{
			
			//mozile.editElement(el.id);
			//mozile.useSchema("lib/xhtml-basic.rng");
			//mozile.useSchema("lib/xhtml/exclude/form.rng");
			//mozile.save.target = el;
			return;
		}
		*/
		
		if(el && el.className && el.className == "front_edit_block")
		{
			var minx = 1000000;
			var miny = 1000000;
			var maxx = 0;
			var maxy = 0;
			
			var els = new Array();
			els = getAllChildren(els, el);
			
			for(var i = 0; i < els.length; i++)
			{
				var x = findPosX(els[i]);
				var y = findPosY(els[i]);
				var w = els[i].offsetWidth;
				var h = els[i].offsetHeight;
				if(x && y)
				{
					minx = Math.min(x, minx);
					miny = Math.min(y, miny);
					if(w && h)
					{
						maxx = Math.max(maxx, x + w);
						maxy = Math.max(maxy, y + h);
					}
				}
			}
			//XXX vielleicht alle elemente einzeln maskieren, wegen absolut positionierten sachen, aber eigentlich sollte das so passen
			//XXX z-index ist jetzt vom overlay und toolbar eine million (+1) aber eigentlich sollte ein check drin sein, was das höchste auf der jewiligen seite ist.
			//XXX die toolbar darf den slice nicht völlig verdecken, wenn der ganz klein sein sollte...
			
			front_edit_toolbar_div.style.left = (maxx - front_edit_toolbar_div.offsetWidth) + "px";
			front_edit_toolbar_div.style.top = (miny + 2) + "px";
			front_edit_toolbar_div.style.visibility = "visible";
			
			front_edit_overlay_div.style.display = 'block';
			front_edit_overlay_div.style.left = minx + "px";
			front_edit_overlay_div.style.top = miny + "px";
			front_edit_overlay_div.style.width = (maxx - minx) + "px";
			front_edit_overlay_div.style.height = (maxy - miny) + "px";
			clickel = el;
		}
		else
		{
			front_edit_toolbar_div.style.visibility = "hidden";
			front_edit_overlay_div.style.display = 'none';
			document.getElementById("front_edit_moduleselect").style.display = "none";
			clickel = null;
		}
	}
}

function doclick(ev)
{
	/*
	var el;
	if(ev != -123)
	{
		if(!ev)
		{
			ev = window.event;
			el = ev.srcElement;
		}
		else
		{
			el = ev.target;
		}
		while(el.className != "front_edit_block" && el.className != "front_end_edit_text" && el.parentNode)
		{
			el = el.parentNode;
		}
		
		if(el.className == "front_end_edit_text")
		{
			
			//mozile.editElement(el.id);
			//mozile.useSchema("lib/xhtml-basic.rng");
			//mozile.useSchema("lib/xhtml/exclude/form.rng");
			//mozile.save.target = el;
			return;
		}
	}
	*/
	
	if(ev == -123)
	{
		clickel = null;
	}
	
	if(oldel && clickel != oldel)
	{
		loadIntoDiv(oldel, "redaxo/index.php?page=front_edit&subpage=content&article_id="+old_article_id+"&slice_id="+old_slice_id);
	}
	
	if(clickel && clickel.className == "front_edit_block")
	{
		oldel = clickel;	
		var rel = clickel.getAttribute("rel");
		rel = rel.replace(/,/g, "&");
		var vars = rel.split("&");
		for(var i = 0; i < vars.length; i++)
		{
			var pair = vars[i].split("=");
			if(pair[0] == "article_id")
				old_article_id = pair[1];
			else if(pair[0] == "slice_id")
				old_slice_id = pair[1];
		}
		openWin('redaxo/index.php?page=front_edit&mode=edit&' + rel + '&function=edit');
	}
}

function openWin(url)
{
	var front_edit_window = window.open(url, 'front_edit_window', 'width=800,height=250,scrollbars=yes');
	front_edit_window.focus();
}

function getAllChildren(ret, el)
{
	for(var i = 0; i < el.childNodes.length; i++)
	{
		ret[ret.length] = el.childNodes[i];
		getAllChildren(ret, el.childNodes[i]);
	}
	return ret;
}

function findPosX(obj)
{
	var curleft = 0;
	
	if(obj.offsetParent)
	{
		while(1) 
		{
			curleft += obj.offsetLeft;
			if(!obj.offsetParent)
				break;
			obj = obj.offsetParent;
		}
	}
	else if(obj.x)
	{
		curleft += obj.x;
	}
	
	return curleft;
}

function findPosY(obj)
{
	var curtop = 0;
	
	if(obj.offsetParent)
	{
		while(1)
		{
			curtop += obj.offsetTop;
			if(!obj.offsetParent)
				break;
			obj = obj.offsetParent;
		}
	}
	else if(obj.y)
	{
		curtop += obj.y;
	}
	
	return curtop;
}

function partOf(child, searchparent)
{
	if(child == searchparent)
		return true;
	
	while(child && (child = child.parentNode))
	{
		if(child == searchparent)
			return true;
	}
	return false;
}

function getScrollOffset()
{
	var x,y;
	if (self.pageYOffset) // all except Explorer
	{
		x = self.pageXOffset;
		y = self.pageYOffset;
	}
	else if (document.documentElement && document.documentElement.scrollTop)
		// Explorer 6 Strict
	{
		x = document.documentElement.scrollLeft;
		y = document.documentElement.scrollTop;
	}
	else if (document.body) // all other Explorers
	{
		x = document.body.scrollLeft;
		y = document.body.scrollTop;
	}
	var ret = new Object();
	ret.x = x;
	ret.y = y;
	return ret;
}

function loadIntoDiv(div, url)
{
	var xh = new XMLHttpRequest();
	xh.open("GET", url, true);
	xh.el = div;
	xh.onreadystatechange = function()
	{
		if(xh.readyState == 4)
		{
			xh.el.innerHTML = xh.responseText;
			xh.el.parentNode.replaceChild(xh.el.firstChild, xh.el);
			redrawOverlay();
		}
	}
	xh.send(null);
}

function loadAndWait(url)
{
	//XXX wir sollten niemals synchron getten, da muss ein schöner ladeschirm mit cancel her!
	var xh = new XMLHttpRequest();
	xh.open("GET", url, false);
	xh.send(null);
}

function reloadCType(article_id, ctype)
{
	loadIntoDiv(document.getElementById("feab_article_"+article_id+"_ctype_"+ctype), "redaxo/index.php?page=front_edit&subpage=content&article_id="+article_id+"&ctype="+ctype);
}

function redrawOverlay()
{
	//XXX sollte eigentlich so tun als ob die maus bewegt worden wäre, ist mir jetzt aber zu blöd, jetzt tuts zumindest das overlay weg, und wenn man die maus bewegt wieder richtig hin.
	var ev = new Object();
	ev.target = clickel;
	front_edit_mousemove(ev);
}

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}
