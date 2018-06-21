<?php
// REDAXO-Properties setzen
$_settings_array = explode("\n", str_replace("\r", '',  $this->getConfig('project_settings')));

$_prefix = '';

foreach ($_settings_array as $_line) {
    if (substr($_line, 0, 1) != '#') { // Kommentarzeilen übergehen
        $_work = explode(' # ', $_line); // wg. Inline-Kommentaren
        $_set = explode(' = ', $_work[0]);

        if (count($_set) === 2) {
            if (trim($_set[0]) == 'PREFIX') { // PREFIX für Properties
                $_prefix = trim($_set[1]);
            } else {
                rex::setProperty($_prefix . trim($_set[0]), trim($_set[1]));
            }
        }
    }
}
