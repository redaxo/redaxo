<?php
/**
 * @var Fragment $this
 * @psalm-scope-this Fragment
 */

use Redaxo\Core\View\Fragment;

?>
<header class="rex-page-header">
    <div class="page-header">
        <h1><?= $this->heading ?>
            <?php if (isset($this->subheading) && '' != $this->subheading): ?>
                <small><?= $this->subheading ?></small>
            <?php endif ?>
        </h1>
    </div>
    <?php if (isset($this->subtitle) && '' != $this->subtitle): ?>
        <?= $this->subtitle ?>
    <?php endif ?>
</header>
