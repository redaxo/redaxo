<?php
// Standard-Werte setzen
if (!$this->hasConfig()) {
    $this->setConfig('project_settings', "# REDAXO-Properties setzen\n# PREFIX = my_\n\nHalloText = Servus Welt!\n");
}
