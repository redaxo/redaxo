
        <nav class="navbar navbar-default navbar-static-top" role="navigation">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="<?= rex_url::backendController() ?>"><?= rex::getServerName() ?></a>
            </div>
            <?= $this->meta_navigation ?>

            <?php if ($this->navigation != ''): ?>
            <div class="navbar-default" role="navigation">
                <div class="navbar-collapse">
                    <?= $this->navigation ?>
                </div>
            </div>
            <?php endif; ?>

        </nav>
