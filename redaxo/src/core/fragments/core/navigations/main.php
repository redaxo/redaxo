<dl class="rex-navi-main">
    <?php if (isset($this->headline)): ?>
    <dt>
        <?php echo $this->headline['title']; ?>
    </dt>
    <?php endif; ?>

    <dd>
        <ul>
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
    </dd>
</dl>
