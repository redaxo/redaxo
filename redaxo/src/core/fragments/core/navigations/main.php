<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 * @var int|null $toggleIndex
 */
$toggleIndex = $this->getVar('toggleIndex');
$toggleAttributes = [];
if (isset($toggleIndex)) {
    $toggleAttributes = [
        'data-toggle' => 'collapse',
        'data-target' => '#nav-pills-' . $toggleIndex,
        'aria-expanded' => 'true',
    ];
}
?>
    <?php if (isset($this->headline)): ?>
        <h4 class="rex-nav-main-title" <?= rex_string::buildAttributes($toggleAttributes) ?>>
            <span><?= $this->headline['title'] ?></span>
            <span class="rex-nav-main-title-icon rex-icon rex-icon-up"></span>
        </h4>
    <?php endif ?>
    <ul class="rex-nav-main-list nav nav-pills nav-stacked <?= isset($toggleIndex) ? ' collapse in" id="nav-pills-' . (int) $toggleIndex . '"' : '"' ?>>
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
