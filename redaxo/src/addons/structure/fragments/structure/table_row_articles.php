<?php if ($this->structure_context->hasCategoryPermission()):?>
    <tr<?=(($this->class_startarticle != '') ? ' class="'.trim($this->class_startarticle).'"' : '');?>>
        <td class="rex-table-icon">
            <a href="<?=$this->editModeUrl;?>" title="<?=htmlspecialchars($this->sql->getValue('name'));?>"><i class="rex-icon<?=$this->class;?>"></i></a>
        </td>
        <td class="rex-table-id" data-title="<?=rex_i18n::msg('header_id');?>"><?=$this->sql->getValue('id');?></td>
        <td data-title="<?=rex_i18n::msg('header_article_name');?>"><a href="<?=$this->editModeUrl;?>"><?=htmlspecialchars($this->sql->getValue('name'));?></a></td>
        <?php if ($this->tmpl_td):?>
            <td data-title="<?=rex_i18n::msg('header_template');?>"><?=$this->tmpl_td;?></td>
        <?php endif;?>
        <td data-title="<?=rex_i18n::msg('header_date');?>"><?=rex_formatter::strftime($this->sql->getDateTimeValue('createdate'), 'date');?></td>
        <td class="rex-table-priority" data-title="<?=rex_i18n::msg('header_priority');?>"><?=htmlspecialchars($this->sql->getValue('priority'));?></td>
        <td class="rex-table-action"><a href="<?=$this->structure_context->getContext()->getUrl(['article_id' => $this->sql->getValue('id'), 'function' => 'edit_art', 'artstart' => $this->structure_context->getArtStart()]);?>"><i class="rex-icon rex-icon-edit"></i> <?=rex_i18n::msg('change');?></a></td>
        <?php if ($this->sql->getValue('startarticle') == 1):?>
            <td class="rex-table-action">
                <span class="text-muted"><i class="rex-icon rex-icon-delete"></i> <?=rex_i18n::msg('delete');?></span>
            </td>
            <td class="rex-table-action">
                <span class="<?=$this->article_class;?> text-muted"><i class="rex-icon <?=$this->article_icon;?>"></i> <?=$this->article_status;?></span>
            </td>
        <?php else: ?>
            <td class="rex-table-action">
                <a href="<?=$this->structure_context->getContext()->getUrl(['article_id' => $this->sql->getValue('id'), 'artstart' => $this->structure_context->getArtStart()] + rex_api_article_delete::getUrlParams());?>" data-confirm="<?=rex_i18n::msg('delete');?>'?"><i class="rex-icon rex-icon-delete"></i> <?=rex_i18n::msg('delete');?></a>
            </td>
            <td class="rex-table-action">
                <?php if ($this->structure_context->hasCategoryPermission() && rex::getUser()->hasPerm('publishArticle[]')):?>
                    <a class="<?=$this->article_class;?>" href="<?=$this->structure_context->getContext()->getUrl(['article_id' => $this->sql->getValue('id'), 'artstart' => $this->structure_context->getArtStart()] + rex_api_article_status::getUrlParams());?>"><i class="rex-icon <?=$this->article_icon;?>"></i> <?=$this->article_status;?></a>
                <?php else:?>
                    <span class="<?=$this->article_class;?> text-muted"><i class="rex-icon <?=$this->article_icon;?>"></i> <?=$this->article_status;?></span>
                <?php endif;?>
            </td>
        <?php endif;?>
    </tr>
<?php else: ?>
    <tr>
        <td class="rex-table-icon"><i class="rex-icon<?=$this->class;?>"></i></td>
        <td class="rex-table-id" data-title="<?=rex_i18n::msg('header_id');?>"><?=$this->sql->getValue('id');?></td>
        <td data-title="<?=rex_i18n::msg('header_article_name');?>"><?=htmlspecialchars($this->sql->getValue('name'));?></td>
        <?php if ($this->tmpl_td):?>
            <td data-title="<?=rex_i18n::msg('header_template');?>"><?=$this->tmpl_td;?></td>
        <?php endif;?>
        <td data-title="<?=rex_i18n::msg('header_date');?>"><?=rex_formatter::strftime($this->sql->getDateTimeValue('createdate'), 'date');?></td>
        <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '">' . htmlspecialchars($this->sql->getValue('priority')) . '</td>
        <td class="rex-table-action"><span class="text-muted"><i class="rex-icon rex-icon-edit"></i> <?=rex_i18n::msg('change');?></span></td>
        <td class="rex-table-action"><span class="text-muted"><i class="rex-icon rex-icon-delete"></i> <?=rex_i18n::msg('delete');?></span></td>
        <td class="rex-table-action"><span class="<?=$this->art_status_class;?> text-muted"><i class="rex-icon <?=$this->art_status_icon;?>"></i> <?=$this->art_status;?></span></td>
    </tr>
<?php endif;?>
