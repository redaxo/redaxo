<?php

/**
 * @package redaxo\metainfo
 */
class rex_input_date extends rex_input
{
    private $yearSelect;
    private $monthSelect;
    private $daySelect;

    public function __construct()
    {
        parent::__construct();

        $this->yearSelect = new rex_select();
        $this->yearSelect->addOptions(range(2005, date('Y') + 10), true);
        $this->yearSelect->setAttribute('class', 'rex-form-select-year');
        $this->yearSelect->setSize(1);

        $this->monthSelect = new rex_select();
        $this->monthSelect->addOptions(range(1, 12), true);
        $this->monthSelect->setAttribute('class', 'rex-form-select-date');
        $this->monthSelect->setSize(1);

        $this->daySelect = new rex_select();
        $this->daySelect->addOptions(range(1, 31), true);
        $this->daySelect->setAttribute('class', 'rex-form-select-date');
        $this->daySelect->setSize(1);
    }

    public function setValue($value)
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('Expecting $value to be an array!');
        }

        foreach (['year', 'month', 'day'] as $reqIndex) {
            if (!isset($value[$reqIndex])) {
                throw new rex_exception('Missing index "' . $reqIndex . '" in $value!');
            }
        }

        $this->yearSelect->setSelected($value['year']);
        $this->monthSelect->setSelected($value['month']);
        $this->daySelect->setSelected($value['day']);

        parent::setValue($value);
    }

    public function setAttribute($name, $value)
    {
        if ($name == 'name') {
            $this->yearSelect->setName($value . '[year]');
            $this->monthSelect->setName($value . '[month]');
            $this->daySelect->setName($value . '[day]');
        } elseif ($name == 'id') {
            $this->yearSelect->setId($value . '_year');
            $this->monthSelect->setId($value . '_month');
            $this->daySelect->setId($value);
        } else {
            $this->yearSelect->setAttribute($name, $value);
            $this->monthSelect->setAttribute($name, $value);
            $this->daySelect->setAttribute($name, $value);
        }

        parent::setAttribute($name, $value);
    }

    public function getDaySelect()
    {
        return $this->daySelect;
    }

    public function getMonthSelect()
    {
        return $this->monthSelect;
    }

    public function getYearSelect()
    {
        return $this->yearSelect;
    }

    public function getHtml()
    {
        return $this->daySelect->get() .
                     $this->monthSelect->get() .
                     $this->yearSelect->get();
    }
}
