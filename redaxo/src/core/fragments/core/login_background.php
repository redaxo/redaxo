<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>
<picture class="rex-background">
    <source
        srcset="
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/davide-cantelli-ajisKc2uuFk-unsplash_2100.avif') ?> 2100w,
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/davide-cantelli-ajisKc2uuFk-unsplash_3300.avif') ?> 3300w"
        sizes="100vw"
        type="image/avif"
    />
    <img
        alt=""
        src="<?= rex_url::pluginAssets('be_style', 'redaxo', 'images/davide-cantelli-ajisKc2uuFk-unsplash_2100.jpg') ?>"
        srcset="
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/davide-cantelli-ajisKc2uuFk-unsplash_2100.jpg') ?> 2100w,
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/davide-cantelli-ajisKc2uuFk-unsplash_3300.jpg') ?> 3300w"
        sizes="100vw"
    />
</picture>

<style>
    #rex-page-login {
        background-color: #6b6e73;
    }
    #rex-page-login .panel-default {
        background-color: rgba(33, 38, 41, 0.9);
    }
    #rex-page-login .btn-primary {
        background-color: #008eff;
        border-color: rgba(0, 0, 0, 0.5);
    }
    #rex-page-login .btn-primary:hover,
    #rex-page-login .btn-primary:focus {
        background-color: #8cc600;
    }
    #rex-page-login .rex-nav-footer ul li {
        padding: 0;
    }
    #rex-page-login .rex-nav-footer a {
        color: rgba(0, 0, 0, 0.8);
        background-color: rgba(255, 255, 255, 0.3);
        padding: 2px 5px;
    }
    #rex-page-login .rex-nav-footer a:hover,
    #rex-page-login .rex-nav-footer a:focus {
        background-color: rgba(255, 255, 255, 0.4);
        text-decoration: none;
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
            <li class="rex-background-credits"><a href="https://unsplash.com/@cant89" target="_blank" rel="noreferrer noopener">Photo by Davide Cantelli on Unsplash</a></li>
        </ul>
    </nav>
</footer>
