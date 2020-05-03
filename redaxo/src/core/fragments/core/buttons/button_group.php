<div class="btn-group<?php echo(isset($this->vertical) && $this->vertical) ? '-vertical' : ''; ?><?php echo(isset($this->size) && '' != trim($this->size)) ? ' btn-group-' . $this->size : ''; ?>">
    <?php echo $this->subfragment('core/buttons/button.php'); ?>
</div>
