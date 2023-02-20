<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>
    <?php if (isset($this->headline)): ?>
    <h4 class="rex-nav-main-title"><?= $this->headline['title'] ?></h4>
    <?php endif ?>
    <ul class="rex-nav-main-list nav nav-pills nav-stacked">
        <?php foreach ($this->items as $item):

            if (isset($item['active']) && $item['active']):
                $item['itemAttr']['class'][] = 'active';
            endif;

            $icon = '';
            if (isset($item['icon']) && '' != $item['icon']):
                if (isset($item['itemAttr']['class'])) {
                    if (is_array($item['itemAttr']['class'])) {
                        $item['itemAttr']['class'] = array_merge($item['itemAttr']['class'], ['rex-has-icon']);
                    } else {
                        $item['itemAttr']['class'] = [$item['itemAttr']['class'], 'rex-has-icon'];
                    }
                } else {
                    $item['itemAttr']['class'] = ['rex-has-icon'];
                }
                $icon = '<i class="' . trim($item['icon']) . '"></i> ';
            endif;

            $itemAttr = isset($item['itemAttr']) ? rex_string::buildAttributes($item['itemAttr']) : '';
            $linkAttr = isset($item['linkAttr']) ? rex_string::buildAttributes($item['linkAttr']) : '';
        ?>

        <li<?= $itemAttr ?>><a href="<?= $item['href'] ?>"<?= $linkAttr ?>><?= $icon . $item['title'] ?></a></li>
        <?php endforeach ?>
    </ul>
