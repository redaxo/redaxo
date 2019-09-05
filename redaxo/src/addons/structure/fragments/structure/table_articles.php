<?php if (in_array($this->structure_context->getFunction(), ['add_art', 'edit_art'])): ?>
    <form action="<?=$this->structure_context->getContext()->getUrl(['artstart' => $this->structure_context->getArtStart()]);?>" method="post">
        <fieldset>
<?php endif;?>

<table class="table table-striped table-hover">
    <thead>
        <?=$this->thead;?>
    </thead>
    <tbody>
        <?=$this->tbody;?>
    </tbody>
</table>

<?php if (in_array($this->structure_context->getFunction(), ['add_art', 'edit_art'])): ?>
        </fieldset>
    </form>
<?php endif;?>

