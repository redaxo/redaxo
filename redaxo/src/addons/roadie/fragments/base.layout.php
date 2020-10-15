<?php
/** @var rex_fragment $this */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?= ($this->escape($this->getVar('title', $this->i18n('roadie')))) ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0-beta.20/dist/shoelace/shoelace.css">
    <script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0-beta.20/dist/shoelace/shoelace.esm.js"></script>

    <style>
    html {
        font-size: inherit;
    }
    </style>
</head>
<body>
    <header>

    </header>

    <nav>

    </nav>

    <main>
        <h1><?= $this->getVar('title') ?></h1>
        <?= $this->getVar('content') ?>
    </main>

    <footer>
        <nav class="footer-menu">
            <sl-button-group>
                <sl-button type="text" href="#rex-start-of-page"><sl-icon name="arrow-up"></sl-icon></sl-button>
                <sl-button type="text" href="https://www.yakamara.de" target="_blank">yakamara.de</sl-button>
                <sl-button type="text" href="https://www.redaxo.org" target="_blank">redaxo.org</sl-button>
                <?php if (rex::getUser() && rex::getUser()->isAdmin()): ?>
                    <sl-button type="text" href="https://www.redaxo.org/doku/master" target="_blank"><?= $this->i18n('footer_doku'); ?></sl-button>
                <?php endif; ?>
                <?php if (rex::getUser()): ?>
                    <sl-button type="text" href="<?= rex_url::backendPage('credits') ?>"><?= $this->i18n('footer_credits') ?></sl-button>
                <?php else: ?>
                    <sl-button type="text" href="https://www.redaxo.org/" target="_blank"><?= $this->i18n('footer_credits') ?></sl-button>
                <?php endif; ?>
            </sl-button-group>
        </nav>
    </footer>
</body>
</html>
