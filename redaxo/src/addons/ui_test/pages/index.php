<?php
/**
 * @package redaxo5
 */

// https://getbootstrap.com/docs/3.4/css
// https://getbootstrap.com/docs/3.4/components
// https://getbootstrap.com/docs/3.4/javascript
?>

<h1>h1. Bootstrap heading <small>Secondary text</small></h1>
<h2>h2. Bootstrap heading <small>Secondary text</small></h2>
<h3>h3. Bootstrap heading <small>Secondary text</small></h3>
<h4>h4. Bootstrap heading <small>Secondary text</small></h4>
<h5>h5. Bootstrap heading <small>Secondary text</small></h5>
<h6>h6. Bootstrap heading <small>Secondary text</small></h6>

<div class="clearfix"></div><br><br>

<p class="lead">Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Duis mollis, est non commodo
    luctus.</p>

<div class="clearfix"></div><br><br>

<blockquote>
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante.</p>
    <footer>Someone famous in <cite title="Source Title">Source Title</cite></footer>
</blockquote>

<div class="clearfix"></div><br><br>

<p>You can use the mark tag to
    <mark>highlight</mark>
    text.
</p>
<p>For example, <code>&lt;section&gt;</code> should be wrapped as inline.</p>
<p>To switch directories, type <kbd>cd</kbd> followed by the name of the directory.<br>
    To edit settings, press <kbd><kbd>ctrl</kbd> + <kbd>,</kbd></kbd></p>

<div class="clearfix"></div><br><br>

<form class="form-inline">
    <div class="form-group">
        <label class="sr-only" for="exampleInputAmount">Amount (in dollars)</label>
        <div class="input-group">
            <div class="input-group-addon">$</div>
            <input type="text" class="form-control" id="exampleInputAmount" placeholder="Amount">
            <div class="input-group-addon">.00</div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Transfer cash</button>
    <span class="help-block">A block of help text that breaks onto a new line and may extend beyond one line.</span>
</form>

<div class="clearfix"></div><br><br>

<div class="form-group has-success has-feedback">
    <label class="control-label" for="inputSuccess2">Input with success</label>
    <input type="text" class="form-control" id="inputSuccess2" aria-describedby="inputSuccess2Status">
    <span class="glyphicon glyphicon-ok form-control-feedback" aria-hidden="true"></span>
    <span id="inputSuccess2Status" class="sr-only">(success)</span>
</div>
<div class="form-group has-warning has-feedback">
    <label class="control-label" for="inputWarning2">Input with warning</label>
    <input type="text" class="form-control" id="inputWarning2" aria-describedby="inputWarning2Status">
    <span class="glyphicon glyphicon-warning-sign form-control-feedback" aria-hidden="true"></span>
    <span id="inputWarning2Status" class="sr-only">(warning)</span>
</div>
<div class="form-group has-error has-feedback">
    <label class="control-label" for="inputError2">Input with error</label>
    <input type="text" class="form-control" id="inputError2" aria-describedby="inputError2Status">
    <span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>
    <span id="inputError2Status" class="sr-only">(error)</span>
</div>
<div class="form-group has-success has-feedback">
    <label class="control-label" for="inputGroupSuccess1">Input group with success</label>
    <div class="input-group">
        <span class="input-group-addon">@</span>
        <input type="text" class="form-control" id="inputGroupSuccess1" aria-describedby="inputGroupSuccess1Status">
    </div>
    <span class="glyphicon glyphicon-ok form-control-feedback" aria-hidden="true"></span>
    <span id="inputGroupSuccess1Status" class="sr-only">(success)</span>
</div>

<div class="clearfix"></div><br><br>

<button type="button" class="btn btn-default">Default</button>
<button type="button" class="btn btn-primary">Primary</button>
<button type="button" class="btn btn-success">Success</button>
<button type="button" class="btn btn-info">Info</button>
<button type="button" class="btn btn-warning">Warning</button>
<button type="button" class="btn btn-danger">Danger</button>
<button type="button" class="btn btn-link">Link</button>

<div class="clearfix"></div><br><br>

<p class="text-muted">Fusce dapibus, tellus ac cursus commodo, tortor mauris nibh.</p>
<p class="text-primary">Fusce dapibus, tellus ac cursus commodo, tortor mauris nibh.</p>
<p class="text-success">Fusce dapibus, tellus ac cursus commodo, tortor mauris nibh.</p>
<p class="text-info">Fusce dapibus, tellus ac cursus commodo, tortor mauris nibh.</p>
<p class="text-warning">Fusce dapibus, tellus ac cursus commodo, tortor mauris nibh.</p>
<p class="text-danger">Fusce dapibus, tellus ac cursus commodo, tortor mauris nibh.</p>

<div class="clearfix"></div><br><br>

<p class="bg-primary">Maecenas sed diam eget risus varius blandit sit amet non magna.</p>
<p class="bg-success">Maecenas sed diam eget risus varius blandit sit amet non magna.</p>
<p class="bg-info">Maecenas sed diam eget risus varius blandit sit amet non magna.</p>
<p class="bg-warning">Maecenas sed diam eget risus varius blandit sit amet non magna.</p>
<p class="bg-danger">Maecenas sed diam eget risus varius blandit sit amet non magna.</p>

<div class="clearfix"></div><br><br>

<button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>

<div class="clearfix"></div><br><br>

<span class="caret"></span>

<div class="clearfix"></div><br><br>

<div class="dropdown">
    <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="true">
        Dropdown
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
        <li><a href="#">Action</a></li>
        <li><a href="#">Another action</a></li>
        <li><a href="#">Something else here</a></li>
        <li role="separator" class="divider"></li>
        <li><a href="#">Separated link</a></li>
        <li class="disabled"><a href="#">Disabled link</a></li>
    </ul>
</div>

<div class="clearfix"></div><br><br>

<div class="btn-group" role="group" aria-label="...">
    <button type="button" class="btn btn-default">Left</button>
    <button type="button" class="btn btn-default">Middle</button>
    <button type="button" class="btn btn-default">Right</button>
</div>

<div class="clearfix"></div><br><br>

<div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
    <div class="btn-group" role="group" aria-label="First group">
        <button type="button" class="btn btn-default">1</button>
        <button type="button" class="btn btn-default">2</button>
        <button type="button" class="btn btn-default">3</button>
        <button type="button" class="btn btn-default">4</button>
    </div>
    <div class="btn-group" role="group" aria-label="Second group">
        <button type="button" class="btn btn-default">5</button>
        <button type="button" class="btn btn-default">6</button>
        <button type="button" class="btn btn-default">7</button>
    </div>
    <div class="btn-group" role="group" aria-label="Third group">
        <button type="button" class="btn btn-default">8</button>
    </div>
</div>

<div class="clearfix"></div><br><br>

<div class="btn-group">
    <button type="button" class="btn btn-danger">Action</button>
    <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
        aria-expanded="false">
        <span class="caret"></span>
        <span class="sr-only">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu">
        <li><a href="#">Action</a></li>
        <li><a href="#">Another action</a></li>
        <li><a href="#">Something else here</a></li>
        <li role="separator" class="divider"></li>
        <li><a href="#">Separated link</a></li>
    </ul>
</div>

<div class="clearfix"></div><br><br>

<ul class="nav nav-tabs">
    <li role="presentation" class="active"><a href="#">Home</a></li>
    <li role="presentation"><a href="#">Profile</a></li>
    <li role="presentation"><a href="#">Messages</a></li>
</ul>

<div class="clearfix"></div><br><br>

<ul class="nav nav-pills">
    <li role="presentation" class="active"><a href="#">Home</a></li>
    <li role="presentation"><a href="#">Profile</a></li>
    <li role="presentation"><a href="#">Messages</a></li>
</ul>

<div class="clearfix"></div><br><br>

<nav aria-label="">
    <ul class="pagination">
        <li class="disabled"><a href="#" aria-label="Previous"><span aria-hidden="true">«</span></a></li>
        <li class="active"><a href="#">1 <span class="sr-only">(current)</span></a></li>
        <li><a href="#">2</a></li>
        <li><a href="#">3</a></li>
        <li><a href="#">4</a></li>
        <li><a href="#">5</a></li>
        <li><a href="#" aria-label="Next"><span aria-hidden="true">»</span></a></li>
    </ul>
</nav>

<div class="clearfix"></div><br><br>

<nav aria-label="">
    <ul class="pager">
        <li><a href="#">Previous</a></li>
        <li><a href="#">Next</a></li>
    </ul>
</nav>

<div class="clearfix"></div><br><br>

<span class="label label-default">Default</span>
<span class="label label-primary">Primary</span>
<span class="label label-success">Success</span>
<span class="label label-info">Info</span>
<span class="label label-warning">Warning</span>
<span class="label label-danger">Danger</span>

<div class="clearfix"></div><br><br>

<a href="#">Inbox <span class="badge">42</span></a>

<button class="btn btn-primary" type="button">
    Messages <span class="badge">4</span>
</button>

<div class="clearfix"></div><br><br>

<div class="jumbotron">
    <h1>Hello, world!</h1>
    <hr>
    <p>This is a simple hero unit, a simple jumbotron-style component for calling extra attention to featured content or information.</p>
</div>

<div class="clearfix"></div><br><br>

<div class="alert alert-success alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
    <strong>Well done!</strong> You successfully read <a href="#" class="alert-link">this important alert
        message</a>.
</div>
<div class="alert alert-info alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
    <strong>Heads up!</strong> This <a href="#" class="alert-link">alert needs your attention</a>, but it's not
    super important.
</div>
<div class="alert alert-warning alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
    <strong>Warning!</strong> Better check yourself, you're <a href="#" class="alert-link">not looking too good</a>.
</div>
<div class="alert alert-danger alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
    <strong>Oh snap!</strong> <a href="#" class="alert-link">Change a few things up</a> and try submitting again.
</div>

<div class="clearfix"></div><br><br>

<div class="progress">
    <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"
        style="width: 60%;">
        60%
    </div>
</div>

<div class="clearfix"></div><br><br>

<div class="progress">
    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40" aria-valuemin="0"
        aria-valuemax="100" style="width: 40%">
        <span class="sr-only">40% Complete (success)</span>
    </div>
</div>
<div class="progress">
    <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="20" aria-valuemin="0"
        aria-valuemax="100" style="width: 20%">
        <span class="sr-only">20% Complete</span>
    </div>
</div>
<div class="progress">
    <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="60" aria-valuemin="0"
        aria-valuemax="100" style="width: 60%">
        <span class="sr-only">60% Complete (warning)</span>
    </div>
</div>
<div class="progress">
    <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="80" aria-valuemin="0"
        aria-valuemax="100" style="width: 80%">
        <span class="sr-only">80% Complete (danger)</span>
    </div>
</div>

<div class="clearfix"></div><br><br>

<div class="progress">
    <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="40"
        aria-valuemin="0" aria-valuemax="100" style="width: 40%">
        <span class="sr-only">40% Complete (success)</span>
    </div>
</div>
<div class="progress">
    <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="20"
        aria-valuemin="0" aria-valuemax="100" style="width: 20%">
        <span class="sr-only">20% Complete</span>
    </div>
</div>
<div class="progress">
    <div class="progress-bar progress-bar-warning progress-bar-striped" role="progressbar" aria-valuenow="60"
        aria-valuemin="0" aria-valuemax="100" style="width: 60%">
        <span class="sr-only">60% Complete (warning)</span>
    </div>
</div>
<div class="progress">
    <div class="progress-bar progress-bar-danger progress-bar-striped" role="progressbar" aria-valuenow="80"
        aria-valuemin="0" aria-valuemax="100" style="width: 80%">
        <span class="sr-only">80% Complete (danger)</span>
    </div>
</div>

<div class="clearfix"></div><br><br>

<div class="list-group">
    <a href="#" class="list-group-item active">
        Cras justo odio
    </a>
    <a href="#" class="list-group-item">Dapibus ac facilisis in</a>
    <a href="#" class="list-group-item">Morbi leo risus</a>
    <a href="#" class="list-group-item">Porta ac consectetur ac</a>
    <a href="#" class="list-group-item">Vestibulum at eros</a>
</div>

<div class="clearfix"></div><br><br>

<ul class="list-group">
    <li class="list-group-item list-group-item-success">Dapibus ac facilisis in</li>
    <li class="list-group-item list-group-item-info">Cras sit amet nibh libero</li>
    <li class="list-group-item list-group-item-warning">Porta ac consectetur ac</li>
    <li class="list-group-item list-group-item-danger">Vestibulum at eros</li>
</ul>
<div class="list-group">
    <a href="#" class="list-group-item list-group-item-success">Dapibus ac facilisis in</a>
    <a href="#" class="list-group-item list-group-item-info">Cras sit amet nibh libero</a>
    <a href="#" class="list-group-item list-group-item-warning">Porta ac consectetur ac</a>
    <a href="#" class="list-group-item list-group-item-danger">Vestibulum at eros</a>
</div>

<div class="clearfix"></div><br><br>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Panel title</h3>
    </div>
    <div class="panel-body">
        Panel content
    </div>
    <div class="panel-footer">
        Panel footer
    </div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">Panel title</h3>
    </div>
    <div class="panel-body">
        Panel content
    </div>
    <div class="panel-footer">
        Panel footer
    </div>
</div>

<div class="panel panel-success">
    <div class="panel-heading">
        <h3 class="panel-title">Panel title</h3>
    </div>
    <div class="panel-body">
        Panel content
    </div>
    <div class="panel-footer">
        Panel footer
    </div>
</div>

<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">Panel title</h3>
    </div>
    <div class="panel-body">
        Panel content
    </div>
    <div class="panel-footer">
        Panel footer
    </div>
</div>

<div class="panel panel-warning">
    <div class="panel-heading">
        <h3 class="panel-title">Panel title</h3>
    </div>
    <div class="panel-body">
        Panel content
    </div>
    <div class="panel-footer">
        Panel footer
    </div>
</div>

<div class="panel panel-danger">
    <div class="panel-heading">
        <h3 class="panel-title">Panel title</h3>
    </div>
    <div class="panel-body">
        Panel content
    </div>
    <div class="panel-footer">
        Panel footer
    </div>
</div>

<div class="clearfix"></div><br><br>

<section class="rex-page-section">
    <div class="panel panel-edit">

        <header class="panel-heading">
            <div class="panel-title">edit</div>
        </header>

        <div class="panel-body">
            Hello I’ve waited here for you <a href="#">Everlong</a> Tonight I throw myself into and out of the red
            <code>Out of her head</code>, she sang Come down and waste away with me Down with me Slow, how you wanted it
            to be I’m over my head Out of her head, she sang And I wonder
        </div>

        <footer class="panel-footer">
            <div class="rex-form-panel-footer">
                <div class="btn-toolbar">
                    <button class="btn btn-save" type="submit" name="" value="">Speichern</button>
                    <button class="btn btn-delete" type="submit" name="" value="">Löschen</button>
                    <button class="btn btn-send" type="submit" name="" value="">Sonstiges</button>
                    <button class="btn btn-abort" type="submit" name="" value="">Abbrechen</button>
                </div>
            </div>
        </footer>
    </div>
</section>

<div class="clearfix"></div><br><br>

<div class="panel panel-default">
    <div class="panel-heading">Panel heading</div>
    <div class="panel-body">
        <p>Some default panel content here. Nulla vitae elit libero, a pharetra augue. Aenean lacinia bibendum nulla sed
            consectetur. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Nullam id dolor
            id nibh ultricies vehicula ut id elit.</p>
    </div>

    <ul class="list-group">
        <li class="list-group-item">Cras justo odio</li>
        <li class="list-group-item">Dapibus ac facilisis in</li>
        <li class="list-group-item">Morbi leo risus</li>
        <li class="list-group-item">Porta ac consectetur ac</li>
        <li class="list-group-item">Vestibulum at eros</li>
    </ul>
</div>

<div class="clearfix"></div><br><br>

<div class="well">Look, I'm in a well!</div>

<div class="clearfix"></div><br><br>

<div class="modal" tabindex="-1" role="dialog" style="display: block; position: relative;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Modal title</h4>
            </div>
            <div class="modal-body">
                <p>One fine body&hellip;</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<div class="clearfix"></div><br><br>

<div class="tooltip left" role="tooltip" style="position: relative; display: inline-block; opacity: 1;">
    <div class="tooltip-arrow"></div>
    <div class="tooltip-inner">
        Tooltip on the left
    </div>
</div>

<div class="clearfix"></div><br><br>

<div class="popover top" style="position: relative; width: 260px; display: block;">
    <div class="arrow"></div>
    <h3 class="popover-title">Popover top</h3>
    <div class="popover-content">
        <p>Sed posuere consectetur est at lobortis. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis
            vestibulum.</p>
    </div>
</div>

<div class="clearfix"></div><br><br>

<div class="btn-group" data-toggle="buttons">
    <label class="btn btn-primary active">
        <input type="checkbox" checked> Checkbox 1 (pre-checked)
    </label>
    <label class="btn btn-primary">
        <input type="checkbox"> Checkbox 2
    </label>
    <label class="btn btn-primary">
        <input type="checkbox"> Checkbox 3
    </label>
</div>

<div class="clearfix"></div><br><br>

<div class="btn-group" data-toggle="buttons">
    <label class="btn btn-primary active">
        <input type="radio" name="options" id="option1" checked> Radio 1 (preselected)
    </label>
    <label class="btn btn-primary">
        <input type="radio" name="options" id="option2"> Radio 2
    </label>
    <label class="btn btn-primary">
        <input type="radio" name="options" id="option3"> Radio 3
    </label>
</div>

<div class="clearfix"></div><br><br>

<div style="padding-bottom: 50vh"></div>
