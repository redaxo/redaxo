<?php

$addon = rex_addon::get('debug');
rex_dir::copy(
    $addon->getPath('vendor/itsgoingd/clockwork/Clockwork/Web/public'),
    $addon->getAssetsPath('clockwork')
);

// if we update clockwork we need to update the file path here, because the hash might changed.
$jsFile = $addon->getAssetsPath('clockwork/js/app.fe8ebfde.js');
$jsContent = rex_file::get($jsFile);

// replace default backend url with REDAXO api function
// part we need to replace can be found here https://github.com/underground-works/clockwork-app/blob/002c06260bda1c0e04ffd12f02a5076b1026ca8a/src/platform/standalone.js#L36
$jsContent = str_replace('window.location.href.split("/").slice(0,-1).join("/")).path()+"/"', 'window.location.href.split("/").slice(0,-1).join("/")).path()+"/index.php?page=structure&rex-api-call=debug&request="', $jsContent);
rex_file::put($jsFile, $jsContent);
