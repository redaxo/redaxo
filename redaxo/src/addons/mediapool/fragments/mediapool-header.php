<?php
$uploadButton = $this->getVar('uploadButton');
$uploadButton['attributes'] = [
    'class' => [
        'btn-default',
        'navbar-btn',
    ],
    'data-toggle' => 'modal',
    'data-target' => '#uploadModal',
];
?>
<nav class="navbar navbar-inverse">
    <div class="container-fluid">
        <div class="navbar-left">
            <?= $this->subfragment('core/buttons/button.php', ['buttons' => [$uploadButton]]) ?>
        </div>
        <div class="navbar-right">
            <form class="navbar-form">
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Search">
                </div>
            </form>
        </div>
        <div class="navbar-right">
            <button class="btn btn-primary navbar-btn" data-toggle="button" data-target="#mediapool-wrapper">Filter</button>
            <div class="btn-group">
                <button class="btn btn-primary navbar-btn" data-toggle="dropdown" role="button">Sortierung <span class="caret"></span></button>
                <ul class="dropdown-menu">
                    <li><a href="#">Nach Namen</a></li>
                    <li><a href="#">...</a></li>
                    <li><a href="#">...</a></li>
                    <li><a href="#">...</a></li>
                </ul>
            </div>
        </div>
        <div class="navbar-right">
            <p class="navbar-text">34 Elemente</p>
        </div>
    </div>
</nav>

<div class="modal fade" id="uploadModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= rex_i18n::msg('pool_file_insert') ?></h4>
            </div>
            <div class="modal-body">
                Formular
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"><?= rex_i18n::msg('pool_file_upload') ?></button>
            </div>
        </div>
    </div>
</div>
