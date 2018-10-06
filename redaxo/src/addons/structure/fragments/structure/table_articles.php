<?php
/** @var rex_structure_data $structure_data */
$structure_data = $this->structure_data;

?>
<?php if ($structure_data->getFunction() == 'add_art' || $structure_data->getFunction() == 'edit_art'): ?>
    <form action="<?=$structure_data->getContext()->getUrl(['artstart' => $structure_data->getArtStart()]);?>" method="post">
        <fieldset>
<?php endif;?>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th class="rex-table-icon">
                <?=$structure_data->getCatPerm() ? '<a href="'.$structure_data->getContext()->getUrl(['function' => 'add_art', 'artstart' => $structure_data->getArtStart()]).'" '.rex::getAccesskey(rex_i18n::msg('article_add'), 'add_2').'><i class="rex-icon rex-icon-add-article"></i></a>' : '';?>
            </th>
            <th class="rex-table-id"><?=rex_i18n::msg('header_id');?></th>
            <th><?=rex_i18n::msg('header_article_name');?></th>
            <?=$this->tmpl_head;?>
            <th><?=rex_i18n::msg('header_date');?></th>
            <th class="rex-table-priority"><?=rex_i18n::msg('header_priority');?></th>
            <th class="rex-table-action" colspan="3"><?=rex_i18n::msg('header_status');?></th>
        </tr>
    </thead>
    <tbody>
        <?=$this->content;?>
    </tbody>
</table>

<?php if ($structure_data->getFunction() == 'add_art' || $structure_data->getFunction() == 'edit_art'): ?>
        </fieldset>
    </form>
<?php endif;?>

