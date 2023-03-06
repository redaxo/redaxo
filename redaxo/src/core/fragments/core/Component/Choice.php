<?php

use Redaxo\Core\Fragment\Component\Choice;
use Redaxo\Core\Fragment\Component\ChoiceType;
use Redaxo\Core\Fragment\Fragment;

/** @var Choice $this */

if (ChoiceType::Select === $this->type) {
    require Fragment::resolvePath('core/Component/ChoiceSelect.php');
} elseif ($this->multiple) {
    require Fragment::resolvePath('core/Component/ChoiceCheckbox.php');
} else {
    require Fragment::resolvePath('core/Component/ChoiceRadio.php');
}
