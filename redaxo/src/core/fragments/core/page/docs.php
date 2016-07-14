<div class="rex-docs<?= $this->getVar('sidebar') ? ' rex-docs-has-sidebar' : '' ?>">
    <?php if ($this->getVar('sidebar')): ?>
        <div class="rex-docs-sidebar"><?= $this->getVar('sidebar') ?></div>
    <?php endif ?>
    <article class="rex-docs-content"><?= $this->getVar('content') ?></article>
</div>
