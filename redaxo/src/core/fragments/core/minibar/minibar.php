<div class="rex-minibar">
    <div class="rex-minibar-mini rex-js-minibar">
        <img class="rex-js-svg rex-minibar-logo" src="<?= rex_url::coreAssets('redaxo-logo.svg') ?>" />
    </div>
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

    <div class="rex-minibar-close rex-js-minibar">
        <img class="rex-js-svg rex-minibar-logo" src="<?= rex_url::coreAssets('redaxo-logo.svg') ?>" />
    </div>
</div>
