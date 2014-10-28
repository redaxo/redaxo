<section class="rex-page-section">
    <?php 
    $header = '';
    if (isset($this->heading) && $this->heading != '') {
        $header .= '<h2>' . $this->heading . '</h2>';
    }
    if (isset($this->header) && $this->header != '') {
        $header .= $this->header;
    }
    echo $header != '' ? '<header class="rex-page-section-header">' . $header . '</header>' : '';
    ?>
    <?php if (isset($this->content) && $this->content != ''): ?>
        <div class="rex-page-section-body">
            <?= $this->content; ?> 
        </div>
    <?php endif; ?>
    <?php if (isset($this->footer) && $this->footer != ''): ?>
        <footer class="rex-page-section-footer">
            <?= $this->footer; ?>
        </footer>
    <?php endif; ?>
</section>