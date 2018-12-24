<tr class="mark<?=$this->meta_buttons != '' ? ' rex-has-metainfo' : '';?>">
    <?php if ($this->structure_context->getFunction() == 'add_cat'): ?>
        <td class="rex-table-icon"><i class="rex-icon rex-icon-category"></i></td>
        <td class="rex-table-id" data-title="<?=rex_i18n::msg('header_id');?>">-</td>
        <td data-title="<?=rex_i18n::msg('header_category');?>">
            <input class="form-control rex-js-autofocus" type="text" name="category-name" />
        </td>
        <td class="rex-table-priority" data-title="<?=rex_i18n::msg('header_priority');?>">
            <input class="form-control" type="text" name="category-position" value="<?=$this->catPager->getRowCount() + 1;?>" />
        </td>
        <td class="rex-table-action">
            <?=$this->meta_buttons;?>
        </td>
        <td class="rex-table-action" colspan="2">
            <?=rex_api_category_add::getHiddenFields();?>
            <input type="hidden" name="parent-category-id" value="<?=$this->structure_context->getCategoryId();?>" />
            <button class="btn btn-save" type="submit" name="category-add-button"<?=rex::getAccesskey(rex_i18n::msg('add_category'), 'save');?>><?=rex_i18n::msg('add_category');?></button>
        </td>
    <?php elseif ($this->structure_context->getFunction() == 'edit_cat' && $this->structure_context->getEditId() == $this->i_category_id):?>
        <?=$this->kat_icon_td;?>
        <td class="rex-table-id" data-title="<?=rex_i18n::msg('header_id');?>"><?=$this->i_category_id;?></td>
        <td data-title="<?=rex_i18n::msg('header_category');?>">
            <input class="form-control rex-js-autofocus" type="text" name="category-name" value="<?=htmlspecialchars($this->KAT->getValue('catname'));?>" autofocus />
        </td>
        <td class="rex-table-priority" data-title="<?=rex_i18n::msg('header_priority');?>">
            <input class="form-control" type="text" name="category-position" value="<?=htmlspecialchars($this->KAT->getValue('catpriority'));?>" />
        </td>
        <td class="rex-table-action">
            <?=$this->meta_buttons;?>
        </td>
        <td class="rex-table-action" colspan="2">
            <?=rex_api_category_edit::getHiddenFields();?>
            <input type="hidden" name="category-id" value="<?=$this->structure_context->getEditId();?>" />
            <button class="btn btn-save" type="submit" name="category-edit-button"<?=rex::getAccesskey(rex_i18n::msg('save_category'), 'save');?>><?=rex_i18n::msg('save_category');?></button>
        </td>
    <?php endif;?>
</tr>
