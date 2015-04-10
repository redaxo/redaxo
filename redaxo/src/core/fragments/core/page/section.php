<section class="rex-page-section">

    <?php if (isset($this->before)) echo $this->before; ?>

    <div class="panel panel-default">

        <?php
        $header = '';
        if (isset($this->title) && $this->title != '') {
            $header .= '<div class="panel-title">' . $this->title . '</div>';
        }
        if (isset($this->heading) && $this->heading != '') {
            $header .= $this->heading;
        }
        echo $header != '' ? '<header class="panel-heading">' . $header . '</header>' : '';
        ?>

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
    </div>


    <?php if (isset($this->after)) echo $this->after; ?>
</section>
