<dl class="rex-navi-main">
    <?php if (isset($this->headline)): ?>
    <dt>
        <?php echo $this->headline['title']; ?>
    </dt>
    <?php endif; ?>

    <dd>
        <ul>
            <?php foreach ($this->items as $item): ?>

            <?php if ($item['active']): ?>
                <?php $item['linkAttr']['class'] = 'rex-active'; ?>
            <?php endif; ?>

            <li<?php echo rex_string::buildAttributes($item['itemAttr']); ?>><a href="<?php echo $item['href']; ?>"<?php echo rex_string::buildAttributes($item['linkAttr']); ?>><?php echo $item['title']; ?></a></li>
            <?php endforeach; ?>
        </ul>
    </dd>
</dl>
