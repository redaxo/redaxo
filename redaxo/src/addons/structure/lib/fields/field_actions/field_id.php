<?php
class rex_structure_field_id extends rex_structure_field_base
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
        if ($this->sql instanceof rex_sql) {
            $id = $this->sql->getValue('id');
        } else {
            $id = '-';
        }

        if (!$this->context->hasCategoryPermission()) {
            return $id;
        }

        return $id;
    }

    /**
     * @return string
     */
    protected function getHeader()
    {
        return rex_i18n::msg('header_id');
    }
}
