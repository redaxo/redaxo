<?php
class rex_structure_field_change extends rex_structure_field_group
{
    /**
     * @return string
     */
    public function get()
    {
        switch ($this->type) {
            case rex_structure_field_group::HEADER:
                return $this->getHeader();
                break;

            case rex_structure_field_group::BODY:
            default:
            return $this->getBody();

        }
    }

    /**
     * @return string
     */
    protected function getBody()
    {
        $change = '<i class="rex-icon rex-icon-edit"></i> '.rex_i18n::msg('change');

        if (!$this->context->hasCategoryPermission()) {
            return '<span class="text-muted">'.$change.'</span>';
        }

        $url = $this->context->getContext()->getUrl([
            'article_id' => $this->sql->getValue('id'),
            'function' => 'edit_art',
            'artstart' => $this->context->getArtStart(),
        ]);

        return '<a href="'.$url.'">'.$change.'</a>';
    }

    /**
     * @return string
     */
    protected function getHeader()
    {
        return rex_i18n::msg('header_status');
    }
}
