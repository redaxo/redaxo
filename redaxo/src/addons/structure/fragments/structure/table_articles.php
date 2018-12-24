<?php if ($this->structure_context->getFunction() == 'add_art' || $this->structure_context->getFunction() == 'edit_art'): ?>
    <form action="<?=$this->structure_context->getContext()->getUrl(['artstart' => $this->structure_context->getArtStart()]);?>" method="post">
        <fieldset>
<?php endif;?>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th class="rex-table-icon">
                <?=$this->structure_context->hasCategoryPermission() ? '<a href="'.$this->structure_context->getContext()->getUrl(['function' => 'add_art', 'artstart' => $this->structure_context->getArtStart()]).'" '.rex::getAccesskey(rex_i18n::msg('article_add'), 'add_2').'><i class="rex-icon rex-icon-add-article"></i></a>' : '';?>
            </th>
            <th class="rex-table-id"><?=rex_i18n::msg('header_id');?></th>
            <th><?=rex_i18n::msg('header_article_name');?></th>
            <?php if ($this->tmpl_head): ?>
                <th><?=$this->tmpl_head;?></th>
            <?php endif;?>
            <th><?=rex_i18n::msg('header_date');?></th>
            <th class="rex-table-priority"><?=rex_i18n::msg('header_priority');?></th>
            <th class="rex-table-action" colspan="3"><?=rex_i18n::msg('header_status');?></th>
        </tr>
    </thead>
    <tbody>
        <?=$this->content;?>
    </tbody>
</table>

<?php if ($this->structure_context->getFunction() == 'add_art' || $this->structure_context->getFunction() == 'edit_art'): ?>
        </fieldset>
    </form>
<?php endif;?>

