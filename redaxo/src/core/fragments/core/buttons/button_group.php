<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>
<div class="btn-group<?php echo (isset($this->vertical) && $this->vertical) ? '-vertical' : ''; ?><?php echo (isset($this->size) && '' != trim($this->size)) ? ' btn-group-' . $this->size : ''; ?>">
    <?php $this->subfragment('core/buttons/button.php'); ?>
</div>
