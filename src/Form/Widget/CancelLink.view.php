<?php

use Redaxo\Core\Form\Widget\CancelLink;
use Redaxo\Core\Translation\Translatable;

use function Redaxo\Core\View\escape;

return static function (CancelLink $link): void { ?>
    <a class="btn btn-abort" href="<?= escape($link->url) ?>"><?= escape(new Translatable('form_abort')->translate()) ?></a>
<?php };
