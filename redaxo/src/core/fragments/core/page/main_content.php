<section class="rex-main-frame">
    <?php if (isset($this->content) && $this->content != '' && isset($this->sidebar) && $this->sidebar != ''): ?>
    <div class="row">
        <div class="col-md-9 rex-main-content">
            <?= $this->content; ?>
        </div>
        <div class="col-md-3 rex-main-sidebar">
            <?= $this->sidebar; ?>
        </div>
    </div>
    <?php elseif (isset($this->content) && $this->content != ''): ?>
    <div class="row">
        <div class="col-md-12 rex-main-content">
            <?= $this->content; ?>
        </div>
    </div>
    <?php endif; ?>
</section>
