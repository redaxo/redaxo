<?php

dump($this->search);

$filterCategory = new rex_media_category_select();
$filterCategory->setSize(1);
$filterCategory->setMultiple();
$filterCategory->setAttributes([
    'name' => 'arg[search][categories][]',
    'id' => 'rex_file_category',
    'class' => 'form-control input-sm selectpicker',
    'data-live-search' => 'true',
    'data-style' => 'btn-default btn-sm',
]);
$filterCategory->setSelected($this->search['categories']);

?><div class="panel panel-default">
    <div class="panel-heading rex-has-panel-options">
        <div class="rex-panel-options">
            <button class="btn label label-info">Löschen</button>
        </div>
        <h4 class="panel-title">
            <a class="small" data-toggle="collapse" href="#collapse1">
                Kategorien
            </a>
        </h4>
    </div>
    <div id="collapse1" class="panel-collapse collapse in">
        <div class="panel-body">
            <div class="form-group">
                <?= $filterCategory->get() ?>
            </div>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading rex-has-panel-options">
        <div class="rex-panel-options">
            <button class="btn label label-info">Löschen</button>
        </div>
        <h4 class="panel-title">
            <a class="small" role="button" data-toggle="collapse" href="#collapse2">
                Tags
            </a>
        </h4>
    </div>
    <div id="collapse2" class="panel-collapse collapse in">
        <div class="panel-body">
            <div class="form-group">
                <input type="text" class="form-control input-sm" placeholder="Search" name="arg[search][tags][]" />
            </div>
            <div>
                <button class="btn label label-default">Tag</button>
                <button class="btn label label-primary">Tag</button>
                <button class="btn label label-default">Tag</button>
                <button class="btn label label-default">Tag</button>
                <button class="btn label label-default">Tag</button>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a class="small" role="button" data-toggle="collapse" href="#collapse3">
                Format
            </a>
        </h4>
    </div>
    <div id="collapse3" class="panel-collapse collapse in">
        <div class="panel-body">
            <div>
                <button class="btn label label-default"><i class="fa fa-picture-o"></i></button>
                <button class="btn label label-default"><i class="fa fa-film"></i></button>
                <button class="btn label label-default"><i class="fa fa-volume-up"></i></button>
                <button class="btn label label-default"><i class="fa fa-bath"></i></button>
                <button class="btn label label-default"><i class="fa fa-file-text"></i></button>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading rex-has-panel-options">
        <div class="rex-panel-options">
            <button class="btn label label-info">Löschen</button>
        </div>
        <h4 class="panel-title">
            <a class="small" role="button" data-toggle="collapse" href="#collapse4">
                Status
            </a>
        </h4>
    </div>
    <div id="collapse4" class="panel-collapse collapse in">
        <div class="panel-body">
            <div class="checkbox">
                <label class="small">
                    <input type="checkbox" value="" />
                    Online
                </label>
            </div>
            <div class="checkbox">
                <label class="small">
                    <input type="checkbox" value="" />
                    Gesperrt
                </label>
            </div>
        </div>
    </div>
</div>
