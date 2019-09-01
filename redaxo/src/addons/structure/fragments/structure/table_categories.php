<?php
/**
 * Category table and header
 *
 * Variables:
 * $this->structure_context  Context data
 * $this->content            Table rows
 */
?>

<?php if (in_array($this->structure_context->getFunction(), ['add_cat', 'edit_cat'])): ?>
    <form action="<?=$this->structure_context->getContext()->getUrl(['catstart' => $this->structure_context->getCatStart()]);?>" method="post">
        <fieldset>
            <input type="hidden" name="edit_id" value="<?=$this->structure_context->getEditId();?>" />
<?php endif;?>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th class="rex-table-icon">
                <?php if ($this->structure_context->hasCategoryPermission()):?>
                    <a href="<?=$this->structure_context->getContext()->getUrl(['function' => 'add_cat', 'catstart' => $this->structure_context->getCatStart()]);?>" <?=rex::getAccesskey(rex_i18n::msg('add_category'), 'add');?>><i class="rex-icon rex-icon-add-category"></i></a>
                <?php endif;?>
            </th>
            <th class="rex-table-id"><?=rex_i18n::msg('header_id');?></th>
            <th><?=rex_i18n::msg('header_category');?></th>
            <th class="rex-table-priority"><?=rex_i18n::msg('header_priority');?></th>
            <th class="rex-table-action" colspan="3"><?=rex_i18n::msg('header_status');?></th>
        </tr>
    </thead>
    <tbody>
        <?php if (rex_category::get($this->structure_context->getCategoryId()) instanceof rex_category): ?>
            <tr>
                <td class="rex-table-icon"><i class="rex-icon rex-icon-open-category"></i></td>
                <td class="rex-table-id">-</td>
                <td data-title="<?=rex_i18n::msg('header_category');?>"><a href="<?=$this->structure_context->getContext()->getUrl(['category_id' => rex_category::get($this->structure_context->getCategoryId())->getParentId()]);?>">..</a></td>
                <td class="rex-table-priority" data-title="<?=rex_i18n::msg('header_priority');?>">&nbsp;</td>
                <td class="rex-table-action" colspan="3">&nbsp;</td>
            </tr>
        <?php endif;?>
        <?=$this->content;?>
    </tbody>
</table>

            <?php if (in_array($this->structure_context->getFunction(), ['add_cat', 'edit_cat'])): ?>
        </fieldset>
    </form>
<?php endif;?>

