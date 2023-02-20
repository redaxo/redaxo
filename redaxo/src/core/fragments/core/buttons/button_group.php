<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>
<div class="btn-group<?= (isset($this->vertical) && $this->vertical) ? '-vertical' : '' ?><?= (isset($this->size) && '' != trim($this->size)) ? ' btn-group-' . $this->size : '' ?>">
    <?php $this->subfragment('core/buttons/button.php') ?>
</div>
