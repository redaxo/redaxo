<?php
/**
 * TinyMCE Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @author andreas[dot]eberhard[at]redaxo[dot]de Andreas Eberhard
 * @author <a href="http://rex.andreaseberhard.de">rex.andreaseberhad.de</a>
 *
 * @author Dave Holloway
 * @author <a href="http://www.GN2-Netwerk.de">www.GN2-Netwerk.de</a>
 *
 * @package redaxo4
 * @version svn:$Id$
 */

/**
 * Returns the content of the given folder
 */
if (!function_exists('a52_readFolder'))
{
	function a52_readFolder($dir)
	{
		if (!is_dir($dir))
		{
			trigger_error('Folder "' . $dir . '" is not available or not a directory');
			return false;
		}

		$hdl = opendir($dir);
		$folder = array ();
		while (false !== ($file = readdir($hdl)))
		{
			$folder[] = $file;
		}

		return $folder;
	}
} // End function_exists

/**
 * Returns the files of the given folder
 */
if (!function_exists('a52_readFolderFiles'))
{
	function a52_readFolderFiles($dir)
	{
		$folder = a52_readFolder($dir);
		$files = array ();

		if (!$folder)
		{
			return false;
		}

		foreach ($folder as $file)
		{
			if (is_file($dir . '/' . $file))
			{
				$files[] = $file;
			}
		}

		return $files;
	}
} // End function_exists

/**
 * Output-Filter für das Backend, TinyMCE in den ausgewählten Seiten integrieren
 */
if (!function_exists('a52_tinymce_opf'))
{
	function a52_tinymce_opf($params)
	{
		global $REX;
		global $rxa_tinymce;
		global $clang;

		$content = $params['subject'];
			
		if ( !strstr($content, '</head>')) {
			return $content;
		}

		// Ausgabe für das Backend aufbereiten
		$n = "\n";
		$t = '  ';
		$cssLink = $n . $t . '<!-- Addon TinyMCE -->';

		// Selectbox Fix für IE6
		$cssLink .= $n . $t . '<!--[if lte IE 6]>';
		$script = $rxa_tinymce['fe_path'] . '/selectfix/select_fix.js';
		$cssLink .= $n . $t . '<script type="text/javascript" src="' . $script . '"></script>';
		$script = $rxa_tinymce['fe_path'] . '/selectfix/select_fix.css';
		$cssLink .= $n . $t . '<link rel="stylesheet" type="text/css" href="' . $script . '">';
		$cssLink .= $n . $t . '<![endif]-->';

		// TinyMCE-HauptScript
		$script = $rxa_tinymce['fe_path'] . '/tiny_mce/tiny_mce.js';
		$script = str_replace('\\', '/', $script);
		$cssLink .= $n . $t . '<script type="text/javascript" src="' . $script . '"></script>';

		// Script für Media
		$script = $rxa_tinymce['fe_path'] . '/tiny_mce/plugins/media/js/rexembed.js';
		$script = str_replace('\\', '/', $script);
		$cssLink .= $n . $t . '<script type="text/javascript" src="' . $script . '"></script>';

		// TinyMCE-InitScript
		$script = $REX['HTDOCS_PATH'] . 'redaxo/index.php?tinymceinit=true';
		$script = str_replace('\\', '/', $script);
		$cssLink .= $n . $t . '<script type="text/javascript" src="' . $script . '" id="TinyMCEInit"></script>' . $n . $n;

		$content = str_replace('</head>', $cssLink . '</head>', $content);
		return $content;
	}
} // End function_exists

/**
 * Extension-Point für Medienpool Button "Hinzufügen und übernehmen"
 */
if (!function_exists('a52_tinymce_output_init'))
{
	function a52_tinymce_output_init()
	{
		global $REX;
		global $rxa_tinymce;
		$clang = 0;

		header('Content-type: application/javascript');

		if (isset($_GET['clang']) and trim($_GET['clang']) <> '' and strlen($_GET['clang']) == 1)
			$clang = $_GET['clang'];
			
?>

/**
 * Callback-Funktion für das oeffnen der Linkmap bzw. Medienpool
 */ 
function rexCustomFileBrowser(field_name, url, type, win)
{
	//alert("Field_Name: " + field_name + "\nURL: " + url + "\nType: " + type + "\nWin: " + win); // debug/testing
	var cmsURL = window.location.toString();
	var urlbase = cmsURL.split("index.php");

	if (type == 'image' || type == 'media')
	{
		nameurl = url.replace('files/', '');
		if (nameurl != '') {
			nameurl = "&subpage=detail&file_name="+nameurl;
		}
		cmsURL = "index.php?page=<?php echo $rxa_tinymce['medienpool']; ?>&tinymce=true&opener_input_field="+field_name+"&clang="+<?php echo $clang; ?>+nameurl;
		popupTitle = 'Medienpool';
	}
	if (type == 'file')
	{
		idurl = url.replace('redaxo://', '');
		if (idurl != '') {
			idurl = "&category_id="+idurl;
		}
		cmsURL = "index.php?page=<?php echo $rxa_tinymce['linkmap']; ?>&tinymce=true&opener_input_field="+field_name+"&clang="+<?php echo $clang; ?>+idurl;
		popupTitle = 'Linkmap';
	}
	cmsURL = urlbase[0] + cmsURL;

	tinyMCE.activeEditor.windowManager.open({
		file : cmsURL,
		title : popupTitle,
		width : 760,  // Your dimensions may differ - toy around with them!
		height : 600,
		resizable : "yes",
		scrollbars : "yes",
		inline : "yes",  // This parameter only has an effect if you use the inlinepopups plugin!
		close_previous : "no"
	}, {
		window : win,
		typeid : type,
		input : field_name
	});

	return false;
}

/**
 * Standard TinyMCE-Konfiguration und tinyMCE.init()
 */ 
<?php			
/*
// wird evtl. noch mal benötigt
function rexCustomURLConverter(url, node, on_save) {
	if (url.substr(0,6) == 'files/')
	{
		url = '../' + url;
	}
	// Return new URL
	return url;
}
*/		

		$tiny = new rexTinyMCEEditor();
		echo $tiny->getConfiguration();
		echo "\n\n" . 'tinyMCE.init(tinyMCEInitArray);' . "\n";
	}
} // End function_exists

/**
 * Extension-Point für Medienpool Button "Hinzufügen und übernehmen"
 */
if (!function_exists('a52_tinymce_mediaadded'))
{
	function a52_tinymce_mediaadded($params)
	{
		global $REX;

		// Status Tinymce, Hinzufügen und Hinzufügen+Übernehmen
		$_SESSION['a52_tinymce'] = 'true';
		$_SESSION['a52_save'] = '';
		if (isset($_POST['save'])) 
			$_SESSION['a52_save'] = $_POST['save'];
		$_SESSION['a52_saveand-exit'] = '';
		if (isset($_POST['saveand-exit'])) 
			$_SESSION['a52_saveand-exit'] = $_POST['saveand-exit'];

		// Dateinamen für Outputfilter merken!
		$_SESSION['a52_media_added_filename'] = $params['filename'];
		$_SESSION['a52_media_added_title'] = $params['title'];
	}
} // End function_exists

/**
 * Output-Filter für Medienpool und Linkmap
 */
if (!function_exists('a52_tinymce_opf_media_linkmap'))
{
	function a52_tinymce_opf_media_linkmap($params)
	{
		global $REX;
		global $rxa_tinymce;
		$n = "\n";

		$page = rex_request('page', 'string');
		$tinymce = rex_request('tinymce', 'string');

		$content = $params['subject'];

		// JavaScript für TinyMCE-Popups
		$scriptoutput = $n . '<script type="text/javascript" src="' . $rxa_tinymce['fe_path'] . '/tiny_mce/tiny_mce_popup.js"></script>';

		// JavaScript für Medienpool
		if ($page == $rxa_tinymce['medienpool'])
		{
			$scriptoutput .= $n . '<script type="text/javascript" src="' . $rxa_tinymce['fe_path'] . '/mediapool.js"></script>';
			// Medium hinzufügen und übernehmen, Fenster schliessen
			if (isset($_SESSION['a52_saveand-exit']) and ($_SESSION['a52_saveand-exit'] <> '')) 
			{
				$scriptoutput .= $n . '<script type="text/javascript">';
				$scriptoutput .= $n . '//<![CDATA[';
				$scriptoutput .= $n . '	selectMedia("'.$_SESSION['a52_media_added_filename'].'", "'.$_SESSION['a52_media_added_title'].'")';
				$scriptoutput .= $n . '//]]>';
				$scriptoutput .= $n . '</script>';
				unset($_SESSION['a52_saveand-exit']);
				unset($_SESSION['a52_media_added_filename']);
				unset($_SESSION['a52_media_added_title']);
			}
		}

		// JavaScript für Linkmap
		if ($page == $rxa_tinymce['linkmap'])
		{
			$scriptoutput .= $n . '<script type="text/javascript" src="' . $rxa_tinymce['fe_path'] . '/linkmap.js"></script>';
		}

		$output = $n . '<!-- Addon TinyMCE - ' . $page . ' -->' . $scriptoutput;

		$search = array();
		$replace = array();

		if ($page == $rxa_tinymce['medienpool'])
		{
			if ($rxa_tinymce['rexversion'] == 32)
			{
				$search[0] = '</head>';
				$replace[0] = $n.$output.$n.$n.$search[0];
				$search[1] = '<input type=hidden name=page value=' . $rxa_tinymce['medienpool'] . '>';
				$replace[1] = $search[1].$n.'<input type="hidden" name="tinymce" value="true" /> <!-- inserted by TinyMCE -->';
				$search[2] = 'page=' . $rxa_tinymce['medienpool'];
				$replace[2] = 'page=' . $rxa_tinymce['medienpool'] . '&amp;tinymce=true';
				$search[3] = '"saveandexit"';
				$replace[3] = '"saveand-exit"';
				$search[4] = '<input type="hidden" name="page" value="' . $rxa_tinymce['medienpool'] . '">';
				$replace[4] = $search[4].$n.'<input type="hidden" name="tinymce" value="true" /> <!-- inserted by TinyMCE -->';
			}
			if ($rxa_tinymce['rexversion'] == 40)
			{
				$search[0] = '<div id="rex-title">';
				$replace[0] = $n.$output.$n.$n.$search[0];
				$search[1] = '<input type="hidden" name="page" value="' . $rxa_tinymce['medienpool'] . '" />';
				$replace[1] = $search[1].$n.'<input type="hidden" name="tinymce" value="true" /> <!-- inserted by TinyMCE -->';
				$search[2] = 'page=' . $rxa_tinymce['medienpool'];
				$replace[2] = 'page=' . $rxa_tinymce['medienpool'] . '&amp;tinymce=true';
				$search[3] = '"saveandexit"';
				$replace[3] = '"saveand-exit"';
			}
			if ($rxa_tinymce['rexversion'] == 41)
			{
				$search[0] = '<div id="rex-title">';
				$replace[0] = $n.$output.$n.$n.$search[0];
				$search[1] = '<input type="hidden" name="page" value="' . $rxa_tinymce['medienpool'] . '" />';
				$replace[1] = $search[1].$n.'<input type="hidden" name="tinymce" value="true" /> <!-- inserted by TinyMCE -->';
				$search[2] = 'page=' . $rxa_tinymce['medienpool'];
				$replace[2] = 'page=' . $rxa_tinymce['medienpool'] . '&amp;tinymce=true';
				$search[3] = '"saveandexit"';
				$replace[3] = '"saveand-exit"';
			}
			if ($rxa_tinymce['rexversion'] >= 42)
			{
				$search[0] = '<div class="rex-form" id="rex-form-mediapool-selectcategory">';
				$replace[0] = $n.$output.$n.$n.$search[0];
				$search[1] = '<input type="hidden" name="page" value="' . $rxa_tinymce['medienpool'] . '" />';
				$replace[1] = $search[1].$n.'<input type="hidden" name="tinymce" value="true" /> <!-- inserted by TinyMCE -->';
				$search[2] = 'page=' . $rxa_tinymce['medienpool'];
				$replace[2] = 'page=' . $rxa_tinymce['medienpool'] . '&amp;tinymce=true';
				$search[3] = '"saveandexit"';
				$replace[3] = '"saveand-exit"';
				$search[4] = '<div class="rex-form" id="rex-form-mediapool-other">';
				$replace[4] = $n.$output.$n.$n.$search[4];
				$search[5] = '<div id="rex-navi-path">';
				$replace[5] = $n.$output.$n.$n.$search[5];
			}
			$content = str_replace($search, $replace, $content);
		}

		if ($page == $rxa_tinymce['linkmap'])
		{
  			if ($rxa_tinymce['rexversion'] == 32)
			{
				$search[0] = '</body>';
				$replace[0] = $n.$output.$n.$n.$search[0];
			}
			if ($rxa_tinymce['rexversion'] == 40)
			{
				$search[0] = '<div class="rex-lmp-pth">';
				$replace[0] = $n.$output.$n.$n.$search[0];
				$search[1] = 'page=linkmap';
				$replace[1] = 'page=linkmap&amp;tinymce=true';
			}
			if ($rxa_tinymce['rexversion'] == 41)
			{
				$search[0] = '<div class="rex-lmp-pth">';
				$replace[0] = $n.$output.$n.$n.$search[0];
				$search[1] = 'page=linkmap';
				$replace[1] = 'page=linkmap&amp;tinymce=true';
			}
			if ($rxa_tinymce['rexversion'] >= 42)
			{
				$search[0] = '<div id="rex-title">';
				$replace[0] = $n.$output.$n.$n.$search[0];
				$search[1] = 'page=linkmap';
				$replace[1] = 'page=linkmap&amp;tinymce=true';
			}
			$content = str_replace($search, $replace, $content);
		}

		return $content;
	}
} // End function_exists
