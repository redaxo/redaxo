<section class="rex-page-section">

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
        if (isset($this->title) && $this->title != '') {
            $header .= '<div class="panel-title">';
            if (isset($this->collapse) && $this->collapse) {
                $header .= '<a' . ($collapsed ? ' class="collapsed"' : '') . ' data-toggle="collapse" href="#' . $collapse_id . '">';
            }
            $header .= $this->title;
            if (isset($this->collapse) && $this->collapse) {
                $header .= '</a>';
            }

            $header .= '</div>';
        }
        if (isset($this->heading) && $this->heading != '') {
            $header .= $this->heading;
        }
        if (isset($this->options) && $this->options != '') {
            $header .= '<div class="rex-panel-options">' . $this->options . '</div>';
        }
        echo $header != '' ? '<header class="panel-heading">' . $header . '</header>' : '';
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
