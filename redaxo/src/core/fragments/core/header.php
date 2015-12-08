
        <nav class="rex-nav-top navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <?php if (rex_be_controller::getCurrentPage() != 'login' && !rex_be_controller::getCurrentPageObject()->isPopup()): ?>
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".rex-nav-main > .navbar-collapse">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                    <?php endif; ?>
                    <?php if (rex_be_controller::getCurrentPageObject()->isPopup()): ?>
                        <span class="navbar-brand"><img class="rex-js-svg rex-redaxo-logo" src="<?= rex_url::coreAssets('redaxo-logo.svg') ?>" /></span>
                    <?php else: ?>
                        <a class="navbar-brand" href="<?= rex_url::backendController() ?>"><img class="rex-js-svg rex-redaxo-logo" src="<?= rex_url::coreAssets('redaxo-logo.svg') ?>" /></a>
                    <?php endif; ?>
                </div>
                <?= $this->meta_navigation ?>
            </div>
        </nav>
