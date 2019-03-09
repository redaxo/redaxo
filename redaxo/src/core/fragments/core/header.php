
        <nav class="rex-nav-top navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <?php if (rex_be_controller::getCurrentPageObject()->isPopup()): ?>
                        <span class="navbar-brand"><img class="rex-js-svg rex-redaxo-logo" src="<?= rex_url::coreAssets('redaxo-logo.svg') ?>" /></span>
                    <?php else: ?>
                        <a class="navbar-brand" href="<?= rex_url::backendController() ?>"><img class="rex-js-svg rex-redaxo-logo" src="<?= rex_url::coreAssets('redaxo-logo.svg') ?>" /></a>
                    <?php endif; ?>
                    <?php if (rex::getUser()->isAdmin() && rex::isDebugMode()): ?>
                        <a class="rex-marker-debugmode" href="<?= rex_url::backendPage('system/settings') ?>" title="Debug mode aktiv">
                            <i class="rex-icon fa-heartbeat"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?= $this->meta_navigation ?>
            </div>
        </nav>
