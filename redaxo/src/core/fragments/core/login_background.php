<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>
<picture class="rex-background">
    <source
        srcset="
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/jr-korpa-9XngoIpxcEo-unsplash_2100.avif') ?> 2100w,
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/jr-korpa-9XngoIpxcEo-unsplash_3300.avif') ?> 3300w"
        sizes="100vw"
        type="image/avif"
    />
    <img
        alt=""
        src="<?= rex_url::pluginAssets('be_style', 'redaxo', 'images/jr-korpa-9XngoIpxcEo-unsplash_2100.jpg') ?>"
        srcset="
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/jr-korpa-9XngoIpxcEo-unsplash_2100.jpg') ?> 2100w,
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/jr-korpa-9XngoIpxcEo-unsplash_3300.jpg') ?> 3300w"
        sizes="100vw"
    />
</picture>

<style>
    #rex-page-login {
        background-color: #06061a;
    }
    #rex-page-login .panel-default {
        background-color: rgba(15, 17, 51, 0.9);
    }
    #rex-page-login .btn-primary {
        background-color: #4700b3;
        border-color: rgba(0, 0, 0, 0.5);
    }
    #rex-page-login .btn-primary:hover,
    #rex-page-login .btn-primary:focus {
        background-color: #5b00bd;
    }
</style>

<script>
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
            <li class="rex-background-credits"><a href="https://unsplash.com/@jrkorpa" target="_blank" rel="noreferrer noopener">Photo by Jr Korpa on Unsplash</a></li>
        </ul>
    </nav>
</footer>
