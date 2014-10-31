<section class="rex-page-section">

    <div class="panel panel-default">

        <?php if (isset($this->before)) echo $this->before; ?>

        <?php
        $header = '';
        if (isset($this->heading) && $this->heading != '') {
            $header .= '<h2>' . $this->heading . '</h2>';
        }
        if (isset($this->header) && $this->header != '') {
            $header .= $this->header;
        }
        echo $header != '' ? '<header class="panel-heading">' . $header . '</header>' : '';
        ?>

        <?php if (isset($this->content) && $this->content != ''): ?>
            <div class="panel-body">
                <?= $this->content; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($this->footer) && $this->footer != ''): ?>
            <footer>
                <?= $this->footer; ?>
            </footer>
        <?php endif; ?>

        <?php if (isset($this->after)) echo $this->after; ?>
    </div>
</section>
