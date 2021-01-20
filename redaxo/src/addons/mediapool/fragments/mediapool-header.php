<?php
$uploadButton = $this->getVar('uploadButton');
$uploadButton['attributes'] = [
    'class' => [
        'btn',
        'btn-apply',
        'navbar-btn',
    ],
    'data-toggle' => 'modal',
    'data-target' => '#uploadModal',
];
?>
<nav class="navbar navbar-inverse">
    <div class="container-fluid">
        <div class="navbar-left">
            <div class="btn-group">
                <?= $this->subfragment('core/buttons/button.php', ['buttons' => [$uploadButton]]) ?>
            </div>

            <div class="btn-group">
                <a class="btn btn-primary navbar-btn" title="Images"><i class="fa fa-picture-o"></i></a>
                <a class="btn btn-primary navbar-btn" title="Movies"><i class="fa fa-film"></i></a>
                <a class="btn btn-primary navbar-btn" title="Audios"><i class="fa fa-volume-up"></i></a>
                <a class="btn btn-primary navbar-btn" title="Vector - Path files"><i class="fa fa-bath"></i></a>
                <a class="btn btn-primary navbar-btn" title="Documents"><i class="fa fa-file-text"></i></a>
            </div>
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
                <button class="btn btn-primary navbar-btn dropdown-toggle" data-toggle="dropdown" role="button">Sortierung <span class="caret"></span></button>
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
        <div class="navbar-right">
            <div class="btn-group">
                <a class="btn btn-primary navbar-btn"><i class="fa fa-list"></i></a>
                <a class="btn btn-primary navbar-btn"><i class="fa fa-th"></i></a>
                <a class="btn btn-primary navbar-btn"><i class="fa fa-th-large"></i></a>
            </div>
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
