<?php if ($this->structure_context->hasCategoryPermission()): ?>
    <?php if ($this->structure_context->getFunction() == 'add_art'): ?>
        <tr class="mark">
            <td class="rex-table-icon"><i class="rex-icon rex-icon-article"></i></td>
            <td class="rex-table-id" data-title="<?=rex_i18n::msg('header_id');?>">-</td>
            <td data-title="<?=rex_i18n::msg('header_article_name');?>">
                <input class="form-control" type="text" name="article-name" autofocus />
            </td>
            <?php if ($this->tmpl_td):?>
                <td data-title="<?=rex_i18n::msg('header_template');?>"><?=$this->tmpl_td;?></td>
            <?php endif;?>
            <td data-title="<?=rex_i18n::msg('header_date');?>"><?=rex_formatter::strftime(time(), 'date');?></td>
            <td class="rex-table-priority" data-title="<?=rex_i18n::msg('header_priority');?>">
                <input class="form-control" type="text" name="article-position" value="<?=$this->artPager->getRowCount() + 1;?>" />
            </td>
            <td class="rex-table-action" colspan="3">
                <?=rex_api_article_add::getHiddenFields();?>
                <button class="btn btn-save" type="submit" name="artadd_function"<?=rex::getAccesskey(rex_i18n::msg('article_add'), 'save');?>><?=rex_i18n::msg('article_add');?></button>
            </td>
        </tr>
    <?php elseif ($this->structure_context->getFunction() == 'edit_art' && $this->sql->getValue('id') == $this->structure_context->getArticleId()):?>
        <tr class="mark<?=$this->class_startarticle;?>">
            <td class="rex-table-icon">
                <a href="<?=$this->structure_context->getContext()->getUrl(['page' => 'content/edit', 'article_id' => $this->sql->getValue('id')]);?>" title="<?=htmlspecialchars($this->sql->getValue('name'));?>"><i class="rex-icon<?=$this->class;?>"></i></a>
            </td>
            <td class="rex-table-id" data-title="<?=rex_i18n::msg('header_id');?>"><?=$this->sql->getValue('id');?></td>
            <td data-title="<?=rex_i18n::msg('header_article_name');?>">
                <input class="form-control" type="text" name="article-name" value="<?=htmlspecialchars($this->sql->getValue('name'));?>" autofocus />
            </td>
            <?php if ($this->tmpl_td):?>
                <td data-title="<?=rex_i18n::msg('header_template');?>"><?=$this->tmpl_td;?></td>
            <?php endif;?>
            <td data-title="<?=rex_i18n::msg('header_date');?>">
                <?=rex_formatter::strftime($this->sql->getDateTimeValue('createdate'), 'date');?>
            </td>
            <td class="rex-table-priority" data-title="<?=rex_i18n::msg('header_priority');?>">
                <input class="form-control" type="text" name="article-position" value="<?=htmlspecialchars($this->sql->getValue('priority'));?>" />
            </td>
            <td class="rex-table-action" colspan="3">
                <?=rex_api_article_edit::getHiddenFields();?>
                <button class="btn btn-save" type="submit" name="artedit_function"<?=rex::getAccesskey(rex_i18n::msg('article_save'), 'save');?>><?=rex_i18n::msg('article_save');?></button>
            </td>
        </tr>
    <?php endif;?>
<?php endif;?>
