<section class="mediapool-wrapper toggled" id="mediapool-wrapper">
    <section class="mediapool-content">
        <?= $this->subfragment('mediapool-header.php') ?>
        <?= $this->subfragment('mediapool-list.php') ?>
    </section>
    <aside class="mediapool-sidebar">
        <div class="panel-group" id="accordion">
            <div class="panel panel-default">
                <div class="panel-heading rex-has-panel-options" id="headingOne">
                    <div class="rex-panel-options">
                        <button class="btn label label-info">Löschen</button>
                    </div>
                    <h4 class="panel-title">
                        <a class="small" data-toggle="collapse" href="#collapseOne">
                            Kategorien
                        </a>
                    </h4>
                </div>
                <div id="collapseOne" class="panel-collapse collapse in">
                    <div class="panel-body">
                        <div class="form-group">
                            <input type="text" class="form-control input-sm" placeholder="Search">
                        </div>
                        <div>
                            <button class="btn label label-default">Default</button>
                            <button class="btn label label-default">Default</button>
                            <button class="btn label label-default">Default</button>
                            <button class="btn label label-primary">Default</button>
                            <button class="btn label label-default">Default</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading rex-has-panel-options" id="headingTwo">
                    <div class="rex-panel-options">
                        <button class="btn label label-info">Löschen</button>
                    </div>
                    <h4 class="panel-title">
                        <a class="small" role="button" data-toggle="collapse" href="#collapseTwo">
                            Tags
                        </a>
                    </h4>
                </div>
                <div id="collapseTwo" class="panel-collapse collapse in">
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
                <div class="panel-heading" role="tab" id="headingThree">
                    <h4 class="panel-title">
                        <a class="small" role="button" data-toggle="collapse" href="#collapseThree">
                            Status
                        </a>
                    </h4>
                </div>
                <div id="collapseThree" class="panel-collapse collapse">
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
        </div>
    </aside>
</section>


<script>
    $('[data-toggle="button"]').on('click', function (event) {
        event.preventDefault();
        let target = $(this).data('target');
        $(target).toggleClass('toggled');
    });
</script>
