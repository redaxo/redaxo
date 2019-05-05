<?php

/**
 * @internal
 */
class rex_event_select extends rex_select
{
    public function __construct($options)
    {
        parent::__construct();
        $this->setMultiple(1);
        foreach ($options as $key => $value) {
            $this->addOption($value, $key);
        }
        $this->setSize(count($options));
    }
}
