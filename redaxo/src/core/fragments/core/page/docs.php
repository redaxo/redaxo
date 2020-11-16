<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>
<div class="rex-docs">
    <?php if ($this->getVar('sidebar') || $this->getVar('toc')): ?>
        <div class="rex-docs-sidebar">
            <?php if ($this->getVar('toc')): ?>
            <nav class="rex-nav-toc"><?= $this->getVar('toc') ?></nav>
            <?php endif ?>
            <?= $this->getVar('sidebar') ?>
        </div>
    <?php endif ?>
    <article class="rex-docs-content"><?= $this->getVar('content') ?></article>
</div>
