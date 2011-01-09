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
?>

<?php
	$filename = dirname( __FILE__) . '/lang/help.' . $REX['LANG'] . '.lang';
	if(is_readable($filename))
	{
		if (strstr($REX['LANG'], 'utf8'))
		{
			echo nl2br(utf8_encode(file_get_contents($filename)));
		}
		else
		{
			echo nl2br(file_get_contents($filename));
		}
	}
?>

<br />

<?php
	$filename = dirname( __FILE__) . '/_changelog.txt';
	if(is_readable($filename))
	{
		if (strstr($REX['LANG'], 'utf8'))
		{
			echo str_replace('  - ', '&nbsp;&nbsp;-&nbsp;', nl2br(utf8_encode(file_get_contents($filename))));
		}
		else
		{
			echo str_replace('  - ', '&nbsp;&nbsp;-&nbsp;', nl2br(file_get_contents($filename)));
		}
	}
?>

<br /><br />
