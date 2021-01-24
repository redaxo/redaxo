<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>
        <?php if ('' != $this->navigation): ?>
        <nav id="rex-js-nav-main" class="rex-nav-main navbar-default" role="navigation">
            <div class="navbar-collapse collapse">
                <?= $this->navigation ?>
            </div>
        </nav>
        <?php endif;
