<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo\cronjob
 */

class rex_cronjob_form extends rex_form
{
    private $mainFieldset;

    public function __construct($tableName, $fieldset, $whereCondition, $method = 'post', $debug = false)
    {
        parent::__construct($tableName, $fieldset, $whereCondition, $method, $debug);
        $this->mainFieldset = $fieldset;
    }

    public function addIntervalField($name, $value = null, $attributes = [])
    {
        $attributes['internal::fieldClass'] = 'rex_cronjob_form_interval_element';
        $attributes['class'] = 'form-control';
        $field = $this->addField('', $name, $value, $attributes, true);
        return $field;
    }

    protected function save()
    {
        if ($this->isEditMode()) {
            $nexttime = $this->getElement($this->mainFieldset, 'nexttime');
            if (strtotime($nexttime->getValue()) > 0) {
                $interval = $this->getElement($this->mainFieldset, 'interval');
                $nexttime->setValue(rex_sql::datetime(rex_cronjob_manager_sql::calculateNextTime($interval->getValue())));
            }
        }
        $return = parent::save();
        rex_cronjob_manager_sql::factory()->saveNextTime();
        return $return;
    }
}

class rex_cronjob_form_interval_element extends rex_form_element
{

    public function formatElement()
    {
        $name = $this->getAttribute('name') . '[]';
        $value = explode('|', htmlspecialchars($this->getValue()));
        if (count($value) != 4) {
            $value = [null, 1, 'd'];
        }

        $options = [
            'i' => rex_i18n::msg('cronjob_interval_minutes'), 
            'h' => rex_i18n::msg('cronjob_interval_hour'), 
            'd' => rex_i18n::msg('cronjob_interval_day'), 
            'w' => rex_i18n::msg('cronjob_interval_week'), 
            'm' => rex_i18n::msg('cronjob_interval_month'), 
            'y' => rex_i18n::msg('cronjob_interval_year'), 
        ];

        $items = [];
        $buttonLabel = '';
        foreach ($options as $optionValue => $optionTitle) {
            $item = [];
            $item['title'] = $optionTitle;
            $item['href'] = '#';
            $item['attributes']  = 'data-value="' . $optionValue . '"';
            if ($optionValue == $value[2]) {
                $buttonLabel = $optionTitle;
            }
            $items[] = $item;
        }

        $toolbar = '';

        $fragment = new rex_fragment();
        $fragment->setVar('button_label', $buttonLabel);
        $fragment->setVar('items', $items, false);
        $fragment->setVar('group', true);
        $fragment->setVar('right', true);
        $dropdown = $fragment->parse('core/dropdowns/dropdown.php');

        $formElements = [];
        $n = [];
        $n['field'] = '<input class="form-control" type="text" name="' . $name . '" value="' . $value[1] . '" />';
        $n['right'] = $dropdown;
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $field = '<div class="rex-js-cronjob-interval">' . $fragment->parse('core/form/input_group.php') . '<input class="rex-js-cronjob-interval-value" type="hidden" name="' . $name . '" value="' . $value[2] . '" /></div>';

        $javascript = '
        <script type="text/javascript">
        // <![CDATA[
            jQuery(function($){
                $(".rex-js-cronjob-interval .dropdown-menu li a").click(function(event){
                    event.preventDefault();
                    var $title = $(this).text();
                    $(this).closest(".input-group-btn").find(".btn > b").html($title);

                    var $value = $(this).closest("li").attr("data-value");
                    $(".rex-js-cronjob-interval-value").val($value);
                });
            });
        // ]]>
        </script>';

        return $field . $javascript;

    }
}
