<section class="mediapool-wrapper toggled" id="mediapool-wrapper">
    <section class="mediapool-content">
        <?= $this->subfragment('mediapool-header.php') ?>
        <?= $this->subfragment('mediapool-list.php') ?>
    </section>
    <aside class="mediapool-sidebar">
        <div class="panel panel-default">
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
                        <?= $this->getVar('filterCategory') ?>
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
                        <input type="text" class="form-control input-sm" placeholder="Search">
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
    </aside>
</section>


<script>
    $('[data-toggle="button"]').on('click', function (event) {
        event.preventDefault();
        let target = $(this).data('target');
        $(target).toggleClass('toggled');
    });

    $('[data-layout]').on('click', function (event) {
        event.preventDefault();
        let layout = $(this).data('layout');
        let target = $(this).data('target');

        $(target).removeClass(function (index, css) {
            return (css.match (/(^|\s)is-\S+/g) || []).join(' ');
        });

        $(this).parent().children().removeClass('active');
        $(this).addClass('active');
        $(target).addClass(layout);
    });

    $('[data-layout]').first().trigger('click');
</script>
