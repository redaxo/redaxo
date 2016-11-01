<?php

/**
 * Cronjob Addon.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo\cronjob
 *
 * @internal
 */

class rex_cronjob_form extends rex_form
{
    private $mainFieldset;
    /** @var rex_cronjob_form_interval_element */
    private $intervalField;

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
        $this->intervalField = $field;
        return $field;
    }

    protected function save()
    {
        $nexttime = $this->getElement($this->mainFieldset, 'nexttime');
        $timestamp = rex_cronjob_manager_sql::calculateNextTime($this->intervalField->getValue());
        $nexttime->setValue($timestamp ? rex_sql::datetime($timestamp) : null);

        $return = parent::save();
        rex_cronjob_manager_sql::factory()->saveNextTime();
        return $return;
    }
}

/**
 * @package redaxo\cronjob
 *
 * @internal
 */
class rex_cronjob_form_interval_element extends rex_form_element
{
    public function setValue($value)
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        $this->value = $value;
    }

    public function getSaveValue()
    {
        $value = $this->getValue();

        $save = [];
        foreach (['minutes', 'hours', 'days', 'weekdays', 'months'] as $key) {
            if (!isset($value[$key])) {
                $save[$key] = [];
            } elseif ('all' === $value[$key]) {
                $save[$key] = 'all';
            } else {
                $save[$key] = array_map('intval', $value[$key]);
            }
        }

        return json_encode($save);
    }

    public function formatElement()
    {
        $range = function ($low, $high, $step = 1) {
            foreach (range($low, $high, $step) as $i) {
                yield $i => str_pad($i, 2, '0', STR_PAD_LEFT);
            }
        };

        $elements = [];

        $n = [];
        $n['label'] = '<label class="control-label">'.rex_i18n::msg('cronjob_interval_minutes').'</label>';
        $n['field'] = $this->formatField('minutes', rex_i18n::msg('cronjob_interval_minutes_all'), $range(0, 55, 5), [0]);
        $elements[] = $n;

        $n = [];
        $n['label'] = '<label class="control-label">'.rex_i18n::msg('cronjob_interval_hours').'</label>';
        $n['field'] = $this->formatField('hours', rex_i18n::msg('cronjob_interval_hours_all'), $range(0, 23), [0]);
        $elements[] = $n;

        $n = [];
        $n['label'] = '<label class="control-label">'.rex_i18n::msg('cronjob_interval_days').'</label>';
        $n['field'] = $this->formatField('days', rex_i18n::msg('cronjob_interval_days_all'), $range(1, 31));
        $elements[] = $n;

        $n = [];
        $n['label'] = '<label class="control-label">'.rex_i18n::msg('cronjob_interval_weekdays').'</label>';
        $weekdays = function () {
            for ($i = 1; $i < 7; ++$i) {
                yield $i => strftime('%a', strtotime('last sunday +'.$i.' days'));
            }
            yield 0 => strftime('%a', strtotime('last sunday'));
        };
        $n['field'] = $this->formatField('weekdays', rex_i18n::msg('cronjob_interval_weekdays_all'), $weekdays());
        $elements[] = $n;

        $n = [];
        $n['label'] = '<label class="control-label">'.rex_i18n::msg('cronjob_interval_months').'</label>';
        $months = function () {
            for ($i = 1; $i < 13; ++$i) {
                yield $i => strftime('%b', mktime(0, 0, 0, $i, 1));
            }
        };
        $n['field'] = $this->formatField('months', rex_i18n::msg('cronjob_interval_months_all'), $months());
        $elements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $elements, false);
        $element = $fragment->parse('core/form/form.php');

        $element .= '
            <script type="text/javascript">
            // <![CDATA[
                jQuery(function($){
                    $(".rex-js-cronjob-interval-all").each(function () {
                        var $this = $(this);
                        var $checkbox = $this.find(":checkbox");
                        var $particularBox = $this.next();
                        var update = function () {
                            var checked = $checkbox.is(":checked");
                            $particularBox.toggle(!checked);
                            $particularBox.find(":checkbox").prop("disabled", checked);
                        };
                        update(0);
                        $checkbox.change(update);
                    });
                });
            // ]]>
            </script>';

        return $element;
    }

    protected function formatField($group, $optionAll, $options, $default = 'all')
    {
        $value = $this->getValue();
        $value = isset($value[$group]) ? $value[$group] : $default;

        $field = '<div class="rex-js-cronjob-interval-all rex-cronjob-interval-all">';

        $elements = [];

        $id = $this->getAttribute('id').'-'.$group.'-all';
        $name = $this->getAttribute('name').'['.$group.']';
        $checked = 'all' === $value ? ' checked="checked"' : '';

        $elements[] = [
            'label' => '<label class="control-label" for="' . htmlspecialchars($id) . '">' . $optionAll . '</label>',
            'field' => '<input type="checkbox" id="' . htmlspecialchars($id) . '" name="' . htmlspecialchars($name) . '" value="all"' . $checked . ' />',
        ];

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $elements, false);
        $fragment->setVar('grouped', true);
        $field .= $fragment->parse('core/form/checkbox.php');

        $field .= '</div><div class="rex-js-cronjob-interval-particular rex-cronjob-interval-' . $group . '">';

        $elements = [];
        foreach ($options as $key => $label) {
            $id = $this->getAttribute('id').'-'.$group.'-'.$key;
            $name = $this->getAttribute('name').'['.$group.'][]';
            $checked = is_array($value) && in_array($key, $value) ? ' checked="checked"' : '';

            $elements[] = [
                'label' => '<label class="control-label" for="' . htmlspecialchars($id) . '">' . $label . '</label>',
                'field' => '<input type="checkbox" id="' . htmlspecialchars($id) . '" name="' . htmlspecialchars($name) . '" value="' . $key . '"' . $checked . ' />',
            ];
        }

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $elements, false);
        $fragment->setVar('grouped', true);
        $fragment->setVar('inline', true);
        $field .= $fragment->parse('core/form/checkbox.php');

        $field .= '</div>';

        return $field;
    }

    protected function getFragment()
    {
        return 'core/form/container.php';
    }
}
