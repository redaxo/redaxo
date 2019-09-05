<?php
class rex_structure_field_createdate extends rex_structure_field_base
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
            $createdate = rex_formatter::strftime($this->sql->getDateTimeValue('createdate'), 'date');
        } else {
            $createdate = rex_formatter::strftime(time(), 'date');
        }

        if (!$this->context->hasCategoryPermission()) {
            return $createdate;
        }

        return $createdate;
    }

    /**
     * @return string
     */
    protected function getHeader()
    {
        return rex_i18n::msg('header_date');
    }
}
