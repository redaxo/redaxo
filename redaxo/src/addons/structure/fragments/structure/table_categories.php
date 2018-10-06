<?php
/** @var rex_structure_data $structure_data */
$structure_data = $this->structure_data;

?>
<?php if ($structure_data->getFunction() == 'add_cat' || $structure_data->getFunction() == 'edit_cat'): ?>
    <form action="<?=$structure_data->getContext()->getUrl(['catstart' => $structure_data->getCatStart()]);?>" method="post">
        <fieldset>
            <input type="hidden" name="edit_id" value="<?=$structure_data->getEditId();?>" />
<?php endif;?>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th class="rex-table-icon">
                <?=$structure_data->getCatPerm() ? '<a href="'.$structure_data->getContext()->getUrl(['function' => 'add_cat', 'catstart' => $structure_data->getCatStart()]).'" '.rex::getAccesskey(rex_i18n::msg('add_category'), 'add').'><i class="rex-icon rex-icon-add-category"></i></a>' : '';?>
            </th>
            <th class="rex-table-id"><?=rex_i18n::msg('header_id');?></th>
            <th><?=rex_i18n::msg('header_category');?></th>
            <th class="rex-table-priority"><?=rex_i18n::msg('header_priority');?></th>
            <th class="rex-table-action" colspan="3"><?=rex_i18n::msg('header_status');?></th>
        </tr>
    </thead>
    <tbody>
        <?php if ($structure_data->getCategoryId() != 0 && ($category = rex_category::get($structure_data->getCategoryId()))): ?>
            <tr>
                <td class="rex-table-icon"><i class="rex-icon rex-icon-open-category"></i></td>
                <td class="rex-table-id">-</td>
                <td data-title="<?=rex_i18n::msg('header_category');?>"><a href="<?=$structure_data->getContext()->getUrl(['category_id' => $category->getParentId()]);?>">..</a></td>
                <td class="rex-table-priority" data-title="<?=rex_i18n::msg('header_priority');?>">&nbsp;</td>
                <td class="rex-table-action" colspan="3">&nbsp;</td>
            </tr>
        <?php endif;?>
        <?=$this->content;?>
    </tbody>
</table>

<?php if ($structure_data->getFunction() == 'add_cat' || $structure_data->getFunction() == 'edit_cat'): ?>
        </fieldset>
    </form>
<?php endif;?>

