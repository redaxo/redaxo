<tr>
    <?=$this->kat_icon_td;?>
    <td class="rex-table-id" data-title="<?=rex_i18n::msg('header_id');?>"><?=$this->i_category_id;?></td>
    <td data-title="<?=rex_i18n::msg('header_category');?>"><a href="<?=$this->kat_link;?>"><?=htmlspecialchars($this->KAT->getValue('catname'));?></a></td>
    <td class="rex-table-priority" data-title="<?=rex_i18n::msg('header_priority');?>"><?=htmlspecialchars($this->KAT->getValue('catpriority'));?></td>

    <?php if ($this->structure_context->hasCategoryPermission()): ?>
        <td class="rex-table-action">
            <a href="<?=$this->structure_context->getContext()->getUrl(['edit_id' => $this->i_category_id, 'function' => 'edit_cat', 'catstart' => $this->structure_context->getCatStart()]);?>"><i class="rex-icon rex-icon-edit"></i><?=rex_i18n::msg('change');?></a>
        </td>
        <td class="rex-table-action">
            <a href="<?=$this->structure_context->getContext()->getUrl(['category-id' => $this->i_category_id, 'catstart' => $this->structure_context->getCatStart()] + rex_api_category_delete::getUrlParams());?>" data-confirm="<?=rex_i18n::msg('delete');?>?"><i class="rex-icon rex-icon-delete"></i> <?=rex_i18n::msg('delete');?></a>
        </td>
        <td class="rex-table-action">
            <?php if ($this->structure_context->hasCategoryPermission() && rex::getUser()->hasPerm('publishCategory[]')):?>
                <a class="<?=$this->status_class;?>" href="<?=$this->structure_context->getContext()->getUrl(['category-id' => $this->i_category_id, 'catstart' => $this->structure_context->getCatStart()] + rex_api_category_status::getUrlParams());?>"><i class="rex-icon <?=$this->status_icon;?>"></i> <?=$this->kat_status;?></a>
            <?php else:?>
                <span class="<?=$this->status_class;?> text-muted"><i class="rex-icon <?=$this->status_icon;?>"></i> <?=$this->kat_status;?></span>
            <?php endif;?>
        </td>
    <?php elseif (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($this->i_category_id)): ?>
        <td class="rex-table-action">
            <span class="text-muted"><i class="rex-icon rex-icon-edit"></i><?=rex_i18n::msg('change');?></span>
        </td>
        <td class="rex-table-action">
            <span class="text-muted"><i class="rex-icon rex-icon-delete"></i><?=rex_i18n::msg('delete');?></span>
        </td>
        <td class="rex-table-action">
            <span class="<?=$this->status_class;?> text-muted"><i class="rex-icon <?=$this->status_icon;?>"></i><?=$this->kat_status;?></span>
        </td>
    <?php endif;?>
</tr>
