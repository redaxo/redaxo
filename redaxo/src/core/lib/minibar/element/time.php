<?php

/**
 * @package redaxo\core\minibar
 */
class rex_minibar_element_time extends rex_minibar_element
{
    public function render()
    {
        return
        '<div class="rex-minibar-item">
            <span class="rex-minibar-value">
                <span class="rex-js-script-time"><!--DYN-->'.rex_i18n::msg('footer_scripttime', rex::getProperty('timer')->getFormattedDelta(rex_timer::SEC)).'<!--/DYN--></span>
            </span>
        </div>';
    }

    public function getOrientation()
    {
        return rex_minibar_element::RIGHT;
    }
}
