<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>
        <?php if ('' != $this->navigation): ?>
        <div id="rex-js-nav-main" class="rex-nav-main navbar-default">
            <nav class="rex-nav-main-navigation" role="navigation">
                <div>
                    <?= $this->navigation ?>
                </div>
            </nav>
        </div>
        <div id="rex-js-nav-main-backdrop" class="rex-nav-main-backdrop"></div>
        <?php endif;
