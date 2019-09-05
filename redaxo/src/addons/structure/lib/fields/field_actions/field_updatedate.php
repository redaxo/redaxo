<?php
class rex_structure_field_updatedate extends rex_structure_field_base
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
            $updatedate = rex_formatter::strftime($this->sql->getDateTimeValue('updatedate'), 'date');
        } else {
            $updatedate = rex_formatter::strftime(time(), 'date');
        }

        if (!$this->context->hasCategoryPermission()) {
            return $updatedate;
        }

        return $updatedate;
    }

    /**
     * @return string
     */
    protected function getHeader()
    {
        return rex_i18n::msg('header_date');
    }
}
