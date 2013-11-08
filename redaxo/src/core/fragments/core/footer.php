    <footer id="rex-page-footer">
        <div class="rex-navi-footer">
            <ul>
                <li class="top"><a href="" >^</a></li>
                <li class="yakamaracom"><a href="http://www.yakamara.de">yakamara.com</a></li>
                <li class="redaxoorg"><a href="http://www.redaxo.org">redaxo.org</a></li>
                <li><a href="#"><?php echo rex_i18n::msg('footer_joinforum'); ?></a></li>
                <li><a href="<?php if (rex::isSetup()) {
    echo 'http://www.redaxo.org/';
} else {
    echo rex_url::backendPage('credits');
} ?>"><?php echo rex_i18n::msg('footer_credits'); ?></a></li>
                <li><?php echo rex_i18n::msg('footer_datetime', rex_formatter::strftime(time(), 'date')); ?></li>
                <li><?php echo rex_i18n::msg('footer_scripttime', $this->time); ?></li>
            </ul>
        </div>
    </footer>
