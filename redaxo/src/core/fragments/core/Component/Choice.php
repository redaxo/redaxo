<?php
/**
 * @var Choice $this
 * @psalm-scope-this Choice
 */

use Redaxo\Core\Fragment\Component\Choice;
use Redaxo\Core\Fragment\Component\ChoiceType;

$this->type = $this->type ?: ChoiceType::Select;

if (ChoiceType::Check === $this->type && $this->multiple) {
    echo $this->parse('core/Component/ChoiceCheckbox.php');
}
if (ChoiceType::Check === $this->type && !$this->multiple) {
    echo $this->parse('core/Component/ChoiceRadio.php');
}
if (ChoiceType::Select === $this->type) {
    echo $this->parse('core/Component/ChoiceSelect.php');
}
