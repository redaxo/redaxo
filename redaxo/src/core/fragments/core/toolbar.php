<?php

$target = 'rex-js-collapse-' . rand(100, 999) . rand(100, 999);

?>
    
<nav class="navbar navbar-default<?= (isset($this->cssClass) && $this->cssClass != '') ? ' ' . $this->cssClass : ''; ?>">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#<?= $target; ?>">
                <span class="sr-only">Toggle</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <?php if (isset($this->brand) && $this->brand != ''): ?>
                <span class="navbar-brand">
                    <?= $this->brand; ?>
                </span>
            <?php endif; ?>                
        </div>
        <div class="collapse navbar-collapse" id="<?= $target; ?>">
            <?php if (isset($this->content) && $this->content != ''): ?>
                <?= $this->content; ?>
            <?php endif; ?>
        </div>
    </div>
</nav>