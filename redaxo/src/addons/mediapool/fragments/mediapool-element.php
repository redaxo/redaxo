<div class="mediapool-element" data-toggle="button" data-target="#detail-<?= rex_escape(rex_string::normalize($this->getVar('name'), '-')) ?>">
    <div class="mediapool-element-media">
        <?php if (!$this->getVar('exists')): ?>
            // Error - Keine Datei vorhanden
        <?php elseif ($this->getVar('document')): ?>
            // Document
        <?php else: ?>
            <figure class="mediapool-element-image">
                <img src="<?= $this->getVar('url') ?>" alt="" />
                <figcaption class="label label-info"><?= strtoupper($this->getVar('extension')) ?></figcaption>
            </figure>
        <?php endif ?>
    </div>
    <div class="mediapool-element-content">
        <h5 class="mediapool-element-title">
        <?php if ('' !== $this->getVar('title')): ?>
            <?= $this->getVar('title') ?>
        <?php else:  ?>
            <?= $this->getVar('name') ?>
        <?php endif; ?>
        </h5>
        <p class="mediapool-element-footer">
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
