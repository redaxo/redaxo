<?php
use Redaxo\Core\Filesystem\Path;

/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>
<div class="rex-branding">
    <?= rex_file::get(Path::coreAssets('redaxo-logo.svg')) ?>
</div>
