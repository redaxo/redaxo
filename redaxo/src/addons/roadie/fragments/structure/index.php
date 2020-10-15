<?php
/** @var rex_fragment $this */
$context = $this->getVar('context');


$clang = $this->getSubfragment('/macros/clang-switch.php');
$breadcrumb = '<ol><li>'.$this->i18n('root_level').'</li></ol>';

$this->decorate('/base.layout.php', [
    'title' => $this->i18n('title_structure'),
    'content' => $clang.$breadcrumb,
]);
