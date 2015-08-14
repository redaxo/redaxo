
        <?php if ($this->meta_navigation != ''): ?>
        <nav class="rex-nav-top navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".rex-nav-main > .navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="<?= rex_url::backendController() ?>"><img class="rex-js-svg rex-redaxo-logo" src="<?= rex_url::assets('redaxo-logo.svg') ?>" /></a>
                </div>
                <?= $this->meta_navigation ?>
            </div>
        </nav>
        <?php endif; ?>