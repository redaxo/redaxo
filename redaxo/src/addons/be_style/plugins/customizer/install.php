<?php
$default_selectors = 'textarea.rex-code, textarea.codemirror, #rex-rex_cronjob_phpcode textarea, #rex-page-modules-actions textarea, textarea#previewaction , textarea#presaveaction, textarea#postsaveaction';

if (!$this->hasConfig()) {
    $this->setConfig('labelcolor', '#43a047');
    $this->setConfig('codemirror_theme', 'eclipse');
    $this->setConfig('codemirror-selectors', $default_selectors);
    $this->setConfig('codemirror', 1);
    $this->setConfig('codemirror-langs', 0);
    $this->setConfig('codemirror-tools', 0);
    $this->setConfig('showlink', 1);
}
