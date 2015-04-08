
        <nav class="rex-nav-top navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".rex-nav-main">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="<?= rex_url::backendController() ?>"><?= rex::getServerName() ?></a>
                </div>
                <?= $this->meta_navigation ?>
            </div>
        </nav>


        <?php if ($this->navigation != ''): ?>
        <nav class="rex-nav-main navbar-default" role="navigation">
            <div class="navbar-collapse">
                <?= $this->navigation ?>
            </div>
        </nav>
        <?php endif;
