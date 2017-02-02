<div id="content-history-layer" class="history-layer">
    <div class="history-layer-content">
        <div class="row">
            <div class="col-lg-offset-2 col-lg-2 text-center">
                <div class="form-group">
                    <p class="form-control-static"><?php echo rex_i18n::msg("structure_history_current_version"); ?></p>
                    <div class="hide rex-select-style"><?php echo $this->getVar('content1select'); ?></div>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <?php if ( $this->getVar('info') != '') {
    echo '<p class="alert alert-success">' . $this->getVar('info') . '</p>';
} ?>
            </div>
            <div class="col-lg-2 text-center">
                <div class="form-group">
                    <div class="rex-select-style"><?php echo $this->getVar('content2select'); ?></div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-lg-6">
                    <div class="history-responsive-container">
                        <?php echo $this->getVar('content1iframe'); ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="history-responsive-container">
                        <?php echo $this->getVar('content2iframe'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 text-right">
                <div class="form-group">
                    <button class="btn btn-abort" data-history-layer="close"><?php echo rex_i18n::msg("structure_history_close"); ?></button>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <?php echo $this->getVar('button_restore'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="history-layer-background" data-history-layer="close"></div>
</div>
