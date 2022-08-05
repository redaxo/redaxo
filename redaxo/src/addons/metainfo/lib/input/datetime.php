<?php

/**
 * @package redaxo\metainfo
 *
 * @internal
 */
class rex_input_datetime extends rex_input
{
    /** @var rex_input_date */
    private $dateInput;

    /** @var rex_input_time */
    private $timeInput;

    public function __construct()
    {
        parent::__construct();

        $this->dateInput = new rex_input_date();
        $this->timeInput = new rex_input_time();
    }

    /**
     * @return void
     */
    public function setStartYear($startYear)
    {
        $this->dateInput->setStartYear($startYear);
    }

    /**
     * @return void
     */
    public function setEndYear($endYear)
    {
        $this->dateInput->setEndYear($endYear);
    }

    public function setValue($value)
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('Expecting $value to be an array!');
        }

        $this->dateInput->setValue($value);
        $this->timeInput->setValue($value);

        parent::setValue($value);
    }

    /**
     * @return array
     */
    public function getValue()
    {
        return array_merge($this->dateInput->getValue(), $this->timeInput->getValue());
    }

    public function setAttribute($name, $value)
    {
        $this->dateInput->setAttribute($name, $value);
        $this->timeInput->setAttribute($name, $value);

        parent::setAttribute($name, $value);
    }

    /**
     * @return rex_select
     */
    public function getDaySelect()
    {
        return $this->dateInput->getDaySelect();
    }

    /**
     * @return rex_select
     */
    public function getMonthSelect()
    {
        return $this->dateInput->getMonthSelect();
    }

    /**
     * @return rex_select
     */
    public function getYearSelect()
    {
        return $this->dateInput->getYearSelect();
    }

    /**
     * @return rex_select
     */
    public function getHourSelect()
    {
        return $this->timeInput->getHourSelect();
    }

    /**
     * @return rex_select
     */
    public function getMinuteSelect()
    {
        return $this->timeInput->getMinuteSelect();
    }

    public function getHtml()
    {
        return '<span class="rex-form-group-nowrap">' . $this->dateInput->getHtml() . '</span> <span class="rex-form-select-separator">-</span> <span class="rex-form-group-nowrap">' . $this->timeInput->getHTML() . '</span>';
    }
}
