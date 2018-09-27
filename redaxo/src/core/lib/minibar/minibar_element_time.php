<?php

/**
 * @package redaxo\core
 */
class rex_minibar_element_time extends rex_minibar_element
{
    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $date = '';
        if (rex::isBackend()) {
            $date =
                '<span class="rex-minibar-value">
                    '.rex_i18n::msg('footer_datetime', rex_formatter::strftime(time(), 'date')).'
                </span>';
        }
        return
        '<div class="rex-minibar-item">
            '.$date.'
            <span class="rex-minibar-value">
                <span class="rex-js-script-time"><!--DYN-->'.rex_i18n::msg('footer_scripttime', rex::getProperty('timer')->getFormattedDelta(rex_timer::SEC)).'<!--/DYN--></span>
            </span>
        </div>';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrientation()
    {
        return rex_minibar_element::RIGHT;
    }
}
