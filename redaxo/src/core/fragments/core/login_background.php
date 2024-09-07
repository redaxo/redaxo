<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */

$picture_name = 'neom-El92hmAt91o-unsplash';

$url_2400_avif = rex_url::pluginAssets('be_style', 'redaxo', 'images/' . $picture_name . '-2400.avif');
$url_3500_avif = rex_url::pluginAssets('be_style', 'redaxo', 'images/' . $picture_name . '-3500.avif');
$url_2400_webp = rex_url::pluginAssets('be_style', 'redaxo', 'images/' . $picture_name . '-2400.webp');
$url_3500_webp = rex_url::pluginAssets('be_style', 'redaxo', 'images/' . $picture_name . '-3500.webp');

?>
<picture class="rex-background">
    <source
        srcset="
            <?= $url_2400_avif ?> 2400w,
            <?= $url_3500_avif ?> 3500w"
        sizes="100vw"
        type="image/avif"
    />
    <img
        alt=""
        src="<?= $url_2400_webp ?>"
        srcset="
            <?= $url_2400_webp ?> 2400w,
            <?= $url_3500_webp ?> 3500w"
        sizes="100vw"
    />
</picture>

<style nonce="<?= rex_response::getNonce() ?>">
    #rex-page-login {
        background-color: #6b6e73;
    }
    #rex-page-login .panel-default {
        background-color: rgba(33, 38, 41, 0.9);
    }

    #rex-page-login .btn-primary {
        background-color: #006ec6;
        border-color: rgba(0, 0, 0, 0.5);
    }
    #rex-page-login .btn-primary:hover,
    #rex-page-login .btn-primary:focus {
        background-color: #8cc600;
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
            <li class="rex-background-credits">Photo by <a href="https://unsplash.com/de/@neom?utm_content=creditCopyText&utm_medium=referral&utm_source=unsplash" target="_blank" rel="noreferrer noopener">NEOM</a> on <a href="https://unsplash.com/de/fotos/die-sonne-geht-uber-den-bergen-in-der-wuste-unter-El92hmAt91o?utm_content=creditCopyText&utm_medium=referral&utm_source=unsplash"  target="_blank" rel="noreferrer noopener">Unsplash</a></li>
        </ul>
    </nav>
</footer>
