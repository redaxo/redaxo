<?php
/** @todo Styles auslagern */
?>
<div class="card" style="margin: 0 1rem 2rem 1rem; 2px; background: #fff;" data-toggle="button" data-target="#detail-<?= rex_escape(rex_string::normalize($this->getVar('name'), '-')) ?>">
    <div class="card-media">
        <?php if (!$this->getVar('exists')): ?>
            // Error - Keine Datei vorhanden
        <?php elseif ($this->getVar('document')): ?>
            // Document
        <?php else: ?>
            <figure class="card-image" style="position: relative;">
                <img class="img-responsive" src="<?= $this->getVar('url') ?>" alt="" />
                <figcaption class="label label-info" style="position: absolute; right: .5rem; bottom: .5rem;"><?= strtoupper($this->getVar('extension')) ?></figcaption>
            </figure>
        <?php endif ?>
    </div>
    <div class="card-content" style="padding: 1em;">
        <h5 class="card-title">
        <?php if ('' !== $this->getVar('title')): ?>
            <?= $this->getVar('title') ?>
        <?php else:  ?>
            <?= $this->getVar('name') ?>
        <?php endif; ?>
        </h5>
        <p class="text-muted small">
            <time datetime=""><?= $this->getVar('stamp') ?> Uhr</time>
        </p>
    </div>
    <div class="mediapool-detail-wrapper" id="detail-<?= rex_escape(rex_string::normalize($this->getVar('name'), '-')) ?>">
        <div class="mediapool-detail">
            <div class="mediapool-detail-image">
                <?php if ($this->getVar('exists') && !$this->getVar('document')): ?>
                    <img src="<?= $this->getVar('url') ?>" alt="" />
                <?php endif ?>
            </div>
            <aside class="mediapool-detail-sidebar">
                Daten
            </aside>
        </div>
    </div>
</div>
