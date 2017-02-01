    <footer class="rex-global-footer">
        <nav class="rex-nav-footer">
            <ul class="list-inline">
                <li><a href="#rex-start-of-page"><i class="fa fa-arrow-up"></i></a></li>
                <li><a href="https://www.yakamara.de" target="_blank">yakamara.de</a></li>
                <li><a href="https://www.redaxo.org" target="_blank">redaxo.org</a></li>
                <li><a href="https://www.redaxo.org/de/forum/" target="_blank"><?php echo rex_i18n::msg('footer_joinforum'); ?></a></li>
                <li><a href="<?php echo(rex::getUser()) ? rex_url::backendPage('credits') : 'https://www.redaxo.org/" target="_blank' ?>"><?php echo rex_i18n::msg('footer_credits'); ?></a></li>
                <li><?php echo rex_i18n::msg('footer_datetime', rex_formatter::strftime(time(), 'date')); ?></li>
                <li class="rex-js-script-time"><!--DYN--><?php echo rex_i18n::msg('footer_scripttime', $this->time); ?><!--/DYN--></li>
            </ul>
        </nav>
    </footer>
