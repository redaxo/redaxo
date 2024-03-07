<?php
use Redaxo\Core\Filesystem\File;

/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>
<div class="rex-branding">
    <?= File::get(rex_path::coreAssets('redaxo-logo.svg')) ?>
</div>
