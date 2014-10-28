    <?php if (isset($this->headline)): ?>
    <h4 class="rex-nav-main-title"><?= $this->headline['title'] ?></h4>
    <?php endif; ?>
    <ul class="rex-nav-main-list nav">
        <?php foreach ($this->items as $item):

            if (isset($item['active']) && $item['active']):
                $item['linkAttr']['class'][] = 'rex-active';
            endif;

            $itemAttr = isset($item['itemAttr']) ? rex_string::buildAttributes($item['itemAttr']) : '';
            $linkAttr = isset($item['linkAttr']) ? rex_string::buildAttributes($item['linkAttr']) : '';
        ?>

        <li<?= $itemAttr ?>><a href="<?= $item['href']; ?>"<?= $linkAttr; ?>><?= $item['title']; ?></a></li>
        <?php endforeach; ?>
    </ul>
