<?php

namespace Redaxo\Core\Cronjob\Form;

use Redaxo\Core\Database\Sql;
use Redaxo\Core\Form\Form;
use rex_cronjob_manager_sql;

/**
 * @internal
 */
class CronjobForm extends Form
{
    /** @var string */
    private $mainFieldset;
    /** @var IntervalField|null */
    private $intervalField;

    /**
     * @param non-empty-string $tableName
     * @param string $fieldset
     * @param string $whereCondition
     * @param 'post'|'get' $method
     * @param bool $debug
     * @param positive-int $db DB connection ID
     */
    public function __construct($tableName, $fieldset, $whereCondition, $method = 'post', $debug = false, $db = 1)
    {
        parent::__construct($tableName, $fieldset, $whereCondition, $method, $debug, $db);
        $this->mainFieldset = $fieldset;
    }

    /**
     * @param string $name
     *
     * @return IntervalField
     */
    public function addIntervalField($name, $value = null, $attributes = [])
    {
        $attributes['internal::fieldClass'] = IntervalField::class;
        $attributes['class'] = 'form-control';
        /** @var IntervalField $field */
        $field = $this->addField('', $name, $value, $attributes, true);
        $this->intervalField = $field;
        return $field;
    }

    protected function save()
    {
        $nexttime = $this->getElement($this->mainFieldset, 'nexttime');
        $timestamp = rex_cronjob_manager_sql::calculateNextTime($this->intervalField->getIntervalElements());
        $nexttime->setValue($timestamp ? Sql::datetime($timestamp) : null);

        $return = parent::save();
        rex_cronjob_manager_sql::factory()->saveNextTime();
        return $return;
    }
}
