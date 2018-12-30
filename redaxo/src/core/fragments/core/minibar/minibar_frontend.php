<div class="rex-minibar" data-minibar="<?= rex_minibar::getInstance()->isVisible() ? 'true' : 'false' ?>">
    <a class="rex-minibar-opener" href="<?= rex_getUrl('', '', ['visibility' => true] + rex_api_minibar::getUrlParams()) ?>">
        <i class="rex-minibar-opener-icon"></i>
    </a>
    <a class="rex-minibar-close" href="<?= rex_getUrl('', '', ['visibility' => false] + rex_api_minibar::getUrlParams()) ?>">
        <i class="rex-minibar-close-icon"></i>
    </a>
    <div class="rex-minibar-elements">
        <?php
        foreach ($this->elements as $element) {
            $this->subfragment('core/minibar/minibar_element.php', [
                'element' => $element
            ]);
        }
        ?>
    </div>
</div>
