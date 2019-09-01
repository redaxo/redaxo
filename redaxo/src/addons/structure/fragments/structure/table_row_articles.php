<tr<?=(1 == $this->sql->getValue('startarticle') ? ' class=" rex-startarticle"' : '');?>>
    <td class="rex-table-icon">
        <?=rex_structure_action_icon::factory($this->structure_context, $this->sql)->get();?>
    </td>
    <td class="rex-table-id" data-title="<?=rex_i18n::msg('header_id');?>">
        <?=rex_structure_action_id::factory($this->structure_context, $this->sql)->get();?>
    </td>
    <td data-title="<?=rex_i18n::msg('header_article_name');?>">
        <?=rex_structure_action_name::factory($this->structure_context, $this->sql)->get();?>
    </td>
    <?php if (rex_structure_action_template::factory($this->structure_context, $this->sql)->get()):?>
        <td data-title="<?=rex_i18n::msg('header_template');?>">
            <?=rex_structure_action_template::factory($this->structure_context, $this->sql)->get();?>
        </td>
    <?php endif;?>
    <td data-title="<?=rex_i18n::msg('header_date');?>">
        <?=rex_structure_action_createdate::factory($this->structure_context, $this->sql)->get();?>
    </td>
    <td class="rex-table-priority" data-title="<?=rex_i18n::msg('header_priority');?>">
        <?=rex_structure_action_priority::factory($this->structure_context, $this->sql)->get();?>
    </td>
    <td class="rex-table-action">
        <?=rex_structure_action_change::factory($this->structure_context, $this->sql)->get();?>
    </td>
    <td class="rex-table-action">
        <?=rex_structure_action_delete::factory($this->structure_context, $this->sql)->get();?>
    </td>
    <td class="rex-table-action">
        <?=rex_structure_action_status::factory($this->structure_context, $this->sql)->get();?>
    </td>
</tr>
