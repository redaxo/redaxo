<?php

namespace Redaxo\Core\Form\Select;

use function count;

/**
 * @internal
 */
class ActionEventSelect extends Select
{
    public function __construct($options)
    {
        parent::__construct();
        $this->setMultiple();
        foreach ($options as $key => $value) {
            $this->addOption($value, $key);
        }
        $this->setSize(count($options));
    }
}
