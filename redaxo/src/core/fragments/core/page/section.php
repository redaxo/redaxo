<?php
$sectionAttributes = [];
if (isset($this->sectionAttributes)) {
    $sectionAttributes = $this->sectionAttributes;
}
if (isset($sectionAttributes['class']) && !is_array($sectionAttributes['class'])) {
    $sectionAttributes['class'] = [$sectionAttributes['class']];
}
$sectionAttributes['class'][] = 'rex-page-section';

?>
<section<?= rex_string::buildAttributes($sectionAttributes) ?>>

    <?php if (isset($this->before)) {
        echo $this->before;
    } ?>

    <?php if (isset($this->class) && $this->class != ''): ?>
        <div class="panel panel-<?= $this->class; ?>">
    <?php else: ?>
        <div class="panel panel-default">
    <?php endif; ?>

        <?php
        $collapse_id = (isset($this->collapse) && $this->collapse) ? 'collapse-' . rand(100000, 999999) : '';
        $collapsed = (isset($this->collapsed) && $this->collapsed) ? true : false;
        $header = '';

        $attributes = [];
        $attributes['class'][] = 'panel-heading';
        if (isset($this->options) && $this->options != '') {
            $attributes['class'][] = 'rex-has-panel-options';
            $header .= '<div class="rex-panel-options">' . $this->options . '</div>';
        }
        if (isset($this->title) && $this->title != '') {
            $header .= '<div class="panel-title">' . $this->title . '</div>';
        }
        if (isset($this->heading) && $this->heading != '') {
            $header .= $this->heading;
        }
        if (isset($this->collapse) && $this->collapse) {
            if ($collapsed) {
                $attributes['class'][] = 'collapsed';
            }
            $attributes['data-toggle'] = 'collapse';
            $attributes['data-target'] = '#' . $collapse_id;
        };
        echo $header != '' ? '<header' . rex_string::buildAttributes($attributes) . '>' . $header . '</header>' : '';
        ?>

        <?php if (isset($this->collapse) && $this->collapse): ?>
            <div id="<?= $collapse_id; ?>" class="panel-collapse collapse<?= ($collapsed ? '' : ' in'); ?>">
        <?php endif; ?>

        <?php if (isset($this->body) && $this->body != ''): ?>
            <div class="panel-body">
                <?= $this->body; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($this->content) && $this->content != ''): ?>
            <?= $this->content; ?>
        <?php endif; ?>

        <?php if ((isset($this->footer) && $this->footer != '') || (isset($this->buttons) && $this->buttons != '')): ?>
            <footer class="panel-footer">
                <?php if (isset($this->footer) && $this->footer != ''): ?>
                    <?= $this->footer; ?>
                <?php endif; ?>
                <?php if (isset($this->buttons) && $this->buttons != ''): ?>
                    <?= $this->buttons; ?>
                <?php endif; ?>
            </footer>
        <?php endif; ?>

        <?php if (isset($this->collapse) && $this->collapse): ?>
            </div>
        <?php endif; ?>
    </div>


    <?php if (isset($this->after)) {
    echo $this->after;
} ?>
</section>
