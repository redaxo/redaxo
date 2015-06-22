<?php

/**
 * Textile Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

echo rex_view::title('Textile');

$mdl_input = '<?php
if (rex_addon::get(\'textile\')->isAvailable())
{
    echo \'<textarea name="REX_INPUT_VALUE[1]" rows="10">REX_VALUE[1]</textarea><br /><br />\';
    echo \'<a href="#" onclick="jQuery(\\\'#rex-textile-help\\\').toggle(\\\'fast\\\');">Zeige/verberge Textile Hilfe</a><br />\';
    echo \'<div id="rex-textile-help" style="display:none">\';
    rex_textile::showHelpOverview();
    echo \'</div>\';
}
else
{
    echo rex_view::warning(\'Dieses Modul benötigt das "textile" Addon!\');
}
';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('textile_code_for_module_input'));
$fragment->setVar('body', rex_string::highlight($mdl_input), false);
$content = $fragment->parse('core/page/section.php');

echo $content;

$mdl_output = '<?php
if (rex_addon::get(\'textile\')->isAvailable())
{
    if(\'REX_VALUE[id=1 isset=1]\')
    {
        $textile = \'REX_VALUE[id=1 html=1]\';
        $textile = str_replace(\'<br />\', \'\', $textile);
        echo rex_textile::parse($textile);
    }
}
else
{
    echo rex_view::warning(\'Dieses Modul benötigt das "textile" Addon!\');
}
';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('textile_code_for_module_output'));
$fragment->setVar('body', rex_string::highlight($mdl_output), false);
$content = $fragment->parse('core/page/section.php');

echo $content;
