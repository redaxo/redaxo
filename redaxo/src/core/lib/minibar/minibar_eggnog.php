<?php

/**
 *
 * @package redaxo\core
 */
class rex_minibar_eggnog extends rex_minibar_element
{
    /**
     * Returns the html bar item
     *
     * @return string
     */
    public function render()
    {
        $database = rex::getProperty('db');

        return
        '
        <div class="rex-minibar-group">
            <a href="#">
                <span class="rex-minibar-value">
                    '.rex::getVersion().'
                </span>
            </a>
            <div class="rex-minibar-info">
                <div class="rex-minibar-info-group">
                    <div class="rex-minibar-info-piece">
                        <b>REDAXO</b>
                        <span>'.rex::getVersion().' <a href="' . rex_url::backendPage('system/log') . '" title="'.rex_escape(rex_i18n::msg('logfiles')).'">'.rex_i18n::msg('logfiles').'</a></span>
                    </div>
                    <div class="rex-minibar-info-piece">
                        <b>PHP Version</b>
                        <span>'.PHP_VERSION.' <a href="' . rex_url::backendPage('system/phpinfo') . '" title="phpinfo" onclick="newWindow(\'phpinfo\', this.href, 1000,800,\',status=yes,resizable=yes\');return false;">phpinfo()</a></span>
                    </div>
                    <div class="rex-minibar-info-piece">
                        <b>MySQL</b>
                        <span>'.rex_sql::getServerVersion().'</span>
                    </div>
                    <div class="rex-minibar-info-piece">
                        <b>'.rex_i18n::msg('minibar_database').'</b>
                        <span>'.rex_escape($database[1]['name']).'</span>
                    </div>
                    <div class="rex-minibar-info-piece">
                        <b>Host</b>
                        <span>'.$database[1]['host'].'</span>
                    </div>
                </div>
                <div class="rex-minibar-info-group">
                    <div class="rex-minibar-info-piece">
                        <b>'.rex_i18n::msg('minibar_resources').'</b>
                        <span>
                            <a href="https://redaxo.org" rel="help">redaxo.org</a><br />
                            <a href="https://redaxo.org/doku/master" rel="help">'.rex_i18n::msg('minibar_documentation_link_label').'</a>
                        </span>
                    </div>
                    <div class="rex-minibar-info-piece">
                        <b>'.rex_i18n::msg('minibar_help').'</b>
                        <span>
                            <a href="https://redaxo.org/forum/" rel="help">'.rex_i18n::msg('minibar_board_link_label').'</a><br />
                            <a href="https://redaxo.org/slack/" rel="help">'.rex_i18n::msg('minibar_slack_link_label').'</a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        ';
    }

    /**
     * Returns the orientation in the minibar
     *
     * @return string `rex_minibar::LEFT` or `rex_minibar::RIGHT`
     */
    public function getOrientation()
    {
        return rex_minibar::RIGHT;
    }


    /**
     * Returns primary status
     *
     * @return bool
     */
    public function isPrimary()
    {
        return true;
    }
}
