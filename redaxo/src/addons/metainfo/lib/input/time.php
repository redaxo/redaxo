<?php

/**
 * @package redaxo\metainfo
 *
 * @internal
 */
class rex_input_time extends rex_input
{
    private $hourSelect;
    private $minuteSelect;

    public function __construct()
    {
        parent::__construct();

        $this->hourSelect = new rex_select();
        $this->hourSelect->addOptions(range(0, 23), true);
        $this->hourSelect->setSize(1);
        $this->hourSelect->setAttribute('class', 'rex-form-select-date');

        $this->minuteSelect = new rex_select();
        $this->minuteSelect->addOptions(range(0, 59), true);
        $this->minuteSelect->setSize(1);
        $this->minuteSelect->setAttribute('class', 'rex-form-select-date');
    }

    public function setValue($value)
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('Expecting $value to be an array!');
        }

        foreach (['hour', 'minute'] as $reqIndex) {
            if (!isset($value[$reqIndex])) {
                throw new rex_exception('Missing index "' . $reqIndex . '" in $value!');
            }
        }

        $this->hourSelect->setSelected($value['hour']);
        $this->minuteSelect->setSelected($value['minute']);

        parent::setValue($value);
    }

    public function setAttribute($name, $value)
    {
        if ($name == 'name') {
            $this->hourSelect->setName($value . '[hour]');
            $this->minuteSelect->setName($value . '[minute]');
        } elseif ($name == 'id') {
            $this->hourSelect->setId($value . '_hour');
            $this->minuteSelect->setId($value . '_minute');
        } else {
            $this->hourSelect->setAttribute($name, $value);
            $this->minuteSelect->setAttribute($name, $value);
        }
        parent::setAttribute($name, $value);
    }

    public function getHourSelect()
    {
        return $this->hourSelect;
    }

    public function getMinuteSelect()
    {
        return $this->minuteSelect;
    }

    public function getHtml()
    {
        return $this->hourSelect->get() . $this->minuteSelect->get();
    }
}
