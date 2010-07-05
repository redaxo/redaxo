<?php

/**
 * Dient zur Ausgabe des Sprachen-blocks
 * @package redaxo4
 * @version svn:$Id$
 */

// rechte einbauen
// admin[]
// clang[xx], clang[0]
// $REX['USER']->hasPerm("csw[0]")

reset($REX['CLANG']);
$num_clang = count($REX['CLANG']);

if ($num_clang>1)
{
   echo '
<!-- *** OUTPUT OF CLANG-TOOLBAR - START *** -->
   <div id="rex-clang" class="rex-toolbar">
   <div class="rex-toolbar-content">
     <ul>
       <li>'.$I18N->msg("languages").' : </li>';

	 $stop = false;
   $i = 1;
   foreach($REX['CLANG'] as $key => $val)
   {
   	if($i == 1)
   		echo '<li class="rex-navi-first rex-navi-clang-'.$key.'">';
		else
			echo '<li class="rex-navi-clang-'.$key.'">';
		    
    $val = rex_translate($val);

		if (!$REX['USER']->isAdmin() && !$REX['USER']->hasPerm('clang[all]') && !$REX['USER']->hasPerm('clang['. $key .']'))
		{
			echo '<span class="rex-strike">'. $val .'</span>';

			if ($clang == $key) $stop = true;
		}
		else
    {
    	$class = '';
    	if ($key==$clang) $class = ' class="rex-active"';
      echo '<a'.$class.' href="index.php?page='. $REX["PAGE"] .'&amp;clang='. $key . $sprachen_add .'&amp;ctype='. $ctype .'"'. rex_tabindex() .'>'. $val .'</a>';
    }

    echo '</li>';
    $i++;
	}

	echo '
     </ul>
   </div>
   </div>
<!-- *** OUTPUT OF CLANG-TOOLBAR - END *** -->
';

	if ($stop)
	{
		echo '
<!-- *** OUTPUT OF CLANG-VALIDATE - START *** -->
      '. rex_warning('You have no permission to this area') .'
<!-- *** OUTPUT OF CLANG-VALIDATE - END *** -->
';
		require $REX['INCLUDE_PATH']."/layout/bottom.php";
		exit;
	}
}
else
{
	$clang = 0;
}