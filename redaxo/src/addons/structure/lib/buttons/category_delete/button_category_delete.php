<?php
/**
 * @package redaxo\structure
 */
class rex_button_category_delete extends rex_structure_button
{
    public function get()
    {
        $url =  $this->context->getUrl([
            'rex-api-call' => 'category_delete',
            'category-id' => $this->edit_id,
            'catstart' => rex_request('catstart', 'int'),
        ]);

        return '<a href="'.$url.'" data-confirm="'.rex_i18n::msg('delete').' ?"><i class="rex-icon rex-icon-delete"></i> '.rex_i18n::msg('delete').'</a>';
    }
}
