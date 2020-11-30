<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>
        <?php if ('' != $this->navigation): ?>
        <nav id="rex-js-nav-main" class="rex-nav-main navbar-default" role="navigation" data-pjax-container data-pjax-scroll-to="0">
            <div class="rex-nav-main-navigation">
                <?= $this->navigation ?>
            </div>
        </nav>
        <div id="rex-js-nav-main-backdrop" class="rex-nav-main-backdrop"></div>
        <?php endif;
