<?php
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Path;

/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>
<div class="rex-branding">
    <?= File::get(Path::coreAssets('redaxo-logo.svg')) ?>
</div>
