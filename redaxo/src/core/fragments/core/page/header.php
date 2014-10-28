<header class="rex-page-header page-header">
    <h1><?= $this->heading ?> 
        <?php if (isset($this->subheading) && $this->subheading != ''): ?>
            <small><?= $this->subheading ?></small>
        <?php endif; ?>
    </h1>
    <?php if (isset($this->subtitle) && $this->subtitle != ''): ?>
        <?= $this->subtitle ?>
    <?php endif; ?>
</header>