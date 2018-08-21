<div class="rex-minibar" data-minibar="<?= rex_minibar::getInstance()->isVisible() ? 'true' : 'false' ?>">
    <a class="rex-minibar-opener" href="<?= rex_context::fromGet()->getUrl(['visibility' => true] + rex_api_minibar::getUrlParams()) ?>">
        <i class="rex-icon rex-icon-minibar-open"></i>
    </a>
    <a class="rex-minibar-close" href="<?= rex_context::fromGet()->getUrl(['visibility' => false] + rex_api_minibar::getUrlParams()) ?>">
        <i class="rex-icon rex-icon-minibar-close"></i>
    </a>
    <div class="rex-minibar-elements">
        <?php foreach ($this->elements as $element): ?>
        <div class="rex-minibar-element
            <?= ($element->getOrientation() == rex_minibar::RIGHT ? ' rex-minibar-element-right' : '') ?>
            <?= ($element->isDanger() ? ' rex-minibar-status-danger' : '') ?>
            <?= ($element->isWarning() ? ' rex-minibar-status-warning' : '') ?>
            <?= ($element->isPrimary() ? ' rex-minibar-status-primary': '') ?>
        ">
            <?= $element->render() ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
