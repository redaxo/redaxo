<div class="rex-minibar" data-minibar="<?= rex_minibar::isVisible() ? 'true' : 'false' ?>">
    <a class="rex-minibar-opener" href="<?= rex_context::fromGet()->getUrl(['visibility' => true] + rex_api_minibar::getUrlParams()) ?>">
        <i class="rex-icon rex-icon-minibar-open"></i>
    </a>
    <a class="rex-minibar-close" href="<?= rex_context::fromGet()->getUrl(['visibility' => false] + rex_api_minibar::getUrlParams()) ?>">
        <i class="rex-icon rex-icon-minibar-close"></i>
    </a>
    <div class="rex-minibar-items">
        <?php foreach ($this->collectors as $collector): ?>
        <div class="rex-minibar-item
            <?= ($collector->onLeftSide() ? '' : ' rex-minibar-item-right') ?>
            <?= ($collector->isDanger() ? ' rex-minibar-status-danger' : '') ?>
            <?= ($collector->isWarning() ? ' rex-minibar-status-warning' : '') ?>
            <?= ($collector->isPrimary() ? ' rex-minibar-status-primary': '') ?>
        ">
            <?= $collector->getBarItem() ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
