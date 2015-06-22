    <footer class="rex-global-footer">
        <nav class="rex-nav-footer">
            <ul class="list-inline">
                <li><a href="#rex-start-of-page"><i class="fa fa-arrow-up"></i></a></li>
                <li><a href="http://www.yakamara.de">yakamara.de</a></li>
                <li><a href="http://www.redaxo.org">redaxo.org</a></li>
                <li><a href="http://www.redaxo.org/de/forum/"><?php echo rex_i18n::msg('footer_joinforum'); ?></a></li>
                <li><a href="<?php echo(rex::isSetup()) ? 'http://www.redaxo.org/' : rex_url::backendPage('credits') ?>"><?php echo rex_i18n::msg('footer_credits'); ?></a></li>
                <li><?php echo rex_i18n::msg('footer_datetime', rex_formatter::strftime(time(), 'date')); ?></li>
                <li><!--DYN--><?php echo rex_i18n::msg('footer_scripttime', $this->time); ?><!--/DYN--></li>
            </ul>
        </nav>
    </footer>
