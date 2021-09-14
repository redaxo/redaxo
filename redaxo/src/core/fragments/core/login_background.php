<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>
<picture class="rex-background">
    <source
        srcset="
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/nguyen-linh-hUlGJAYAKFo-unsplash_2100.webp') ?> 2100w,
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/nguyen-linh-hUlGJAYAKFo-unsplash_3300.webp') ?> 3300w"
        sizes="100vw"
        type="image/webp"
    />
    <img
        alt=""
        src="<?= rex_url::pluginAssets('be_style', 'redaxo', 'images/nguyen-linh-hUlGJAYAKFo-unsplash_2100.jpg') ?>"
        srcset="
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/nguyen-linh-hUlGJAYAKFo-unsplash_2100.jpg') ?> 2100w,
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/nguyen-linh-hUlGJAYAKFo-unsplash_3300.jpg') ?> 3300w"
        sizes="100vw"
    />
</picture>

<style>
    #rex-page-login .panel-default {
        background-color: rgba(14, 9, 68, 0.8);
    }
    #rex-page-login .btn-primary {
        background-color: #610097;
        border-color: rgba(0, 0, 0, 0.5);
    }
    #rex-page-login .btn-primary:hover,
    #rex-page-login .btn-primary:focus {
        background-color: #7000ae;
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
            <li class="rex-background-credits"><a href="https://unsplash.com/@nklphoto" target="_blank" rel="noreferrer noopener">Photo by Nguyen Linh on Unsplash</a></li>
        </ul>
    </nav>
</footer>
