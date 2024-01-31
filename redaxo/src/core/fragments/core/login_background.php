<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>
<picture class="rex-background">
    <source
        srcset="
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/richard-horvath-cPccYbPrF-A-unsplash-2400.avif') ?> 2400w,
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/richard-horvath-cPccYbPrF-A-unsplash-3500.avif') ?> 3500w"
        sizes="100vw"
        type="image/avif"
    />
    <img
        alt=""
        src="<?= rex_url::pluginAssets('be_style', 'redaxo', 'images/richard-horvath-cPccYbPrF-A-unsplash-2400.webp') ?>"
        srcset="
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/richard-horvath-cPccYbPrF-A-unsplash-2400.webp') ?> 2400w,
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/richard-horvath-cPccYbPrF-A-unsplash-3500.webp') ?> 3500w"
        sizes="100vw"
    />
</picture>

<style nonce="<?= rex_response::getNonce() ?>">
    #rex-page-login {
        background-color: #004eaa;
    }
    #rex-page-login .panel-default {
        background-color: rgba(24, 28, 30, 0.97);
    }

    #rex-page-login .btn-primary {
        background-color: #006ec6;
        border-color: rgba(0, 0, 0, 0.5);
    }
    #rex-page-login .btn-primary:hover,
    #rex-page-login .btn-primary:focus {
        background-color: #0387cc;
    }

    #rex-page-login .rex-global-footer {
        mix-blend-mode: difference;
    }
    @supports (mix-blend-mode: plus-lighter) {
        #rex-page-login .rex-global-footer {
            mix-blend-mode: plus-lighter;
        }
    }
</style>

<script nonce="<?= rex_response::getNonce() ?>">
    var picture = document.querySelector('.rex-background');
    picture.classList.add('rex-background--process');
    picture.querySelector('img').onload = function() {
        picture.classList.add('rex-background--ready');
    }
</script>

<footer class="rex-global-footer">
    <nav class="rex-nav-footer">
        <ul class="list-inline">
            <li><a href="https://www.yakamara.de" target="_blank" rel="noreferrer noopener">yakamara.de</a></li>
            <li><a href="https://www.redaxo.org" target="_blank" rel="noreferrer noopener">redaxo.org</a></li>
            <li class="rex-background-credits"><a href="https://unsplash.com/@ricvath" target="_blank" rel="noreferrer noopener">Photo by Richard Horvath on Unsplash</a></li>
        </ul>
    </nav>
</footer>
