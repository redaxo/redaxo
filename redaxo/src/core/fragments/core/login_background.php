<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>
<picture class="rex-background">
    <source
        srcset="
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/peter-olexa-RYtiT3b7XW4-unsplash_2100.webp') ?> 2100w,
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/peter-olexa-RYtiT3b7XW4-unsplash_3300.webp') ?> 3300w"
        sizes="100vw"
        type="image/webp"
    />
    <img
        alt=""
        src="<?= rex_url::pluginAssets('be_style', 'redaxo', 'images/peter-olexa-RYtiT3b7XW4-unsplash_2100.jpg') ?>"
        srcset="
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/peter-olexa-RYtiT3b7XW4-unsplash_2100.jpg') ?> 2100w,
            <?= rex_url::pluginAssets('be_style', 'redaxo', 'images/peter-olexa-RYtiT3b7XW4-unsplash_3300.jpg') ?> 3300w"
        sizes="100vw"
    />
</picture>

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
            <li class="rex-background-credits"><a href="https://unsplash.com/@deeezyfree" target="_blank" rel="noreferrer noopener">Photo by Peter Olexa on Unsplash</a></li>
        </ul>
    </nav>
</footer>
