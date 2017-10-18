<?php
/**
 * @package redaxo\structure
 */
class rex_structure_category_delete extends rex_fragment
{
    /**
     * @return string
     */
    public function get()
    {
        if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($this->edit_id)) {
            return '';
        }

        $url_params = array_merge($this->url_params, [
            'rex-api-call' => 'category_delete',
            'category-id' => $this->edit_id,
        ]);

        return '<a class="btn btn-default" href="'.$this->context->getUrl($url_params).'" data-confirm="'.rex_i18n::msg('delete').'?" title="'.rex_i18n::msg('delete').'"><i class="rex-icon rex-icon-delete"></i></a>';
    }
}
