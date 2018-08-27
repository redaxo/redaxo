<div class="rex-minibar rex-minibar-backend">
    <div class="rex-minibar-elements">
        <?php foreach ($this->elements as $element): ?>
        <div class="rex-minibar-element
            <?= rex_string::normalize(get_class($element), '-') ?>
            <?= ($element->getOrientation() == rex_minibar_element::RIGHT ? ' rex-minibar-element-right' : '') ?>
            <?= ($element->isDanger() ? ' rex-minibar-status-danger' : '') ?>
            <?= ($element->isWarning() ? ' rex-minibar-status-warning' : '') ?>
            <?= ($element->isPrimary() ? ' rex-minibar-status-primary' : '') ?>
        ">
            <?= $element->render() ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
