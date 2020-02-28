<?php

$addon = rex_addon::get('debug');
rex_dir::copy(
    $addon->getPath('vendor/itsgoingd/clockwork/Clockwork/Web/public'),
    $addon->getAssetsPath('clockwork')
);

$jsFile = $addon->getAssetsPath('clockwork/js/app.fe8ebfde.js');
$jsContent = rex_file::get($jsFile);

// replace default backend url with REDAXO api function
$jsContent = str_replace('window.location.href.split("/").slice(0,-1).join("/")).path()+"/"', 'window.location.href.split("/").slice(0,-1).join("/")).path()+"/index.php?page=structure&rex-api-call=debug&request="', $jsContent);
rex_file::put($jsFile, $jsContent);
