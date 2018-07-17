<div class="rex-debug-bar">
    <div class="rex-debug-bar-mini rex-js-debug-bar">
        <img class="rex-js-svg rex-debug-bar-logo" src="<?= rex_url::coreAssets('redaxo-logo.svg') ?>" />
    </div>
    <?php foreach ($this->collectors as $collector): ?>
    <div class="rex-debug-bar-item
        <?= ($collector->onLeftSide() ? '' : ' rex-debug-bar-item-right') ?>
        <?= ($collector->isDanger() ? ' rex-debug-bar-status-danger' : '') ?>
        <?= ($collector->isWarning() ? ' rex-debug-bar-status-warning' : '') ?>
        <?= ($collector->isPrimary() ? ' rex-debug-bar-status-primary': '') ?>
    ">
        <?= $collector->getBarItem() ?>
    </div>
    <?php endforeach; ?>

    <div class="rex-debug-bar-close rex-js-debug-bar">
        <img class="rex-js-svg rex-debug-bar-logo" src="<?= rex_url::coreAssets('redaxo-logo.svg') ?>" />
    </div>
</div>
