<?php
/**
 * Helper field to ensure the correct recreation of the pre-existing markup
 * May not be necessary later on
 */

class rex_structure_field_action_group extends rex_structure_field_group
{
    /**
     * @var bool
     */
    protected $is_set = false;

    /**
     * @return string
     */
    public function get()
    {
        if (!$this->isCondition()) {
            return '';
        }

        $return = '';
        switch ($this->type) {
            case rex_structure_field_group::HEADER:
                if (!$this->is_set) {
                    $this->is_set = true;
                    $return = $this->getHeader();
                }

                if (!$this->fragment instanceof rex_fragment) {
                    return $return;
                }

                return $this->fragment
                    ->setVar('fields', $return, false)
                    ->parse();
                break;

            case rex_structure_field_group::BODY:
            default:
                foreach ($this->fields as $field) {
                    if ($field instanceof rex_structure_field_base) {
                        $return .= $field
                            ->setContext($this->context, $this->sql)
                            ->setType($this->type)
                            ->get();
                    }
                }

                return $return;
        }
    }

    /**
     * @return string
     */
    protected function getHeader()
    {
        return rex_i18n::msg('header_status');
    }
}
