REDAXO Default-Theme

<?php
$content = rex_file::getOutput(rex_path::plugin('be_style', 'redaxo', 'assets/icon.css'));

preg_match_all('@^\.rex-icon-(.*?):before@im', $content, $matches, PREG_SET_ORDER);

$icons_used = '';
if (count($matches) > 0) {
    $list = [];
    foreach ($matches as $match) {
        $list[] = '<li><span class="rex-icon rex-icon-' . $match[1] . '"></span> ' . $match[1] . '</li>';
    }

    $icons_used = '<ul class="rex-column">' . implode('', $list) . '</ul>';
}


$icons = ['note' => '\266a', 'note-beamed' => '\266b', 'music' => '\1f3b5', 'search' => '\1f50d', 'flashlight' => '\1f526', 'mail' => '\2709', 'heart' => '\2665', 'heart-empty' => '\2661', 'star' => '\2605', 'star-empty' => '\2606', 'user' => '\1f464', 'users' => '\1f465', 'user-add' => '\e700', 'video' => '\1f3ac', 'picture' => '\1f304', 'camera' => '\1f4f7', 'layout' => '\268f', 'menu' => '\2630', 'check' => '\2713', 'cancel' => '\2715', 'cancel-circled' => '\2716', 'cancel-squared' => '\274e', 'plus' => '\2b', 'plus-circled' => '\2795', 'plus-squared' => '\229e', 'minus' => '\2d', 'minus-circled' => '\2796', 'minus-squared' => '\229f', 'help' => '\2753', 'help-circled' => '\e704', 'info' => '\2139', 'info-circled' => '\e705', 'back' => '\1f519', 'home' => '\2302', 'link' => '\1f517', 'attach' => '\1f4ce', 'lock' => '\1f512', 'lock-open' => '\1f513', 'eye' => '\e70a', 'tag' => '\e70c', 'bookmark' => '\1f516', 'bookmarks' => '\1f4d1', 'flag' => '\2691', 'thumbs-up' => '\1f44d', 'thumbs-down' => '\1f44e', 'download' => '\1f4e5', 'upload' => '\1f4e4', 'upload-cloud' => '\e711', 'reply' => '\e712', 'reply-all' => '\e713', 'forward' => '\27a6', 'quote' => '\275e', 'code' => '\e714', 'export' => '\e715', 'pencil' => '\270e', 'feather' => '\2712', 'print' => '\e716', 'retweet' => '\e717', 'keyboard' => '\2328', 'comment' => '\e718', 'chat' => '\e720', 'bell' => '\1f514', 'attention' => '\26a0', 'alert' => '\1f4a5\'', 'vcard' => '\e722', 'address' => '\e723', 'location' => '\e724', 'map' => '\e727', 'direction' => '\27a2', 'compass' => '\e728', 'cup' => '\2615', 'trash' => '\e729', 'doc' => '\e730', 'docs' => '\e736', 'doc-landscape' => '\e737', 'doc-text' => '\1f4c4', 'doc-text-inv' => '\e731', 'newspaper' => '\1f4f0', 'book-open' => '\1f4d6', 'book' => '\1f4d5', 'folder' => '\1f4c1', 'archive' => '\e738', 'box' => '\1f4e6', 'rss' => '\e73a', 'phone' => '\e73a', 'cog' => '\2699', 'tools' => '\2692', 'share' => '\e73c', 'shareable' => '\e73e', 'basket' => '\e73d', 'bag' => '\1f45c\'', 'calendar' => '\1f4c5', 'login' => '\e740', 'logout' => '\e741', 'mic' => '\1f3a4', 'mute' => '\1f507', 'sound' => '\1f50a', 'volume' => '\e742', 'clock' => '\1f554', 'hourglass' => '\23f3', 'lamp' => '\1f4a1', 'light-down' => '\1f505', 'light-up' => '\1f506', 'adjust' => '\25d1', 'block' => '\1f6ab', 'resize-full' => '\e744', 'resize-small' => '\e746', 'popup' => '\e74c', 'publish' => '\e74d', 'window' => '\e74e', 'arrow-combo' => '\e74f', 'down-circled' => '\e758', 'left-circled' => '\e759', 'right-circled' => '\e75a', 'up-circled' => '\e75b', 'down-open' => '\e75c', 'left-open' => '\e75d', 'right-open' => '\e75e', 'up-open' => '\e75f', 'down-open-mini' => '\e760', 'left-open-mini' => '\e761', 'right-open-mini' => '\e762', 'up-open-mini' => '\e763', 'down-open-big' => '\e764', 'left-open-big' => '\e765', 'right-open-big' => '\e766', 'up-open-big' => '\e767', 'down' => '\2b07', 'left' => '\2b05', 'right' => '\27a1', 'up' => '\2b06', 'down-dir' => '\25be', 'left-dir' => '\25c2', 'right-dir' => '\25b8', 'up-dir' => '\25b4', 'down-bold' => '\e4b0', 'left-bold' => '\e4ad', 'right-bold' => '\e4ae', 'up-bold' => '\e4af', 'down-thin' => '\2193', 'left-thin' => '\2190', 'right-thin' => '\2192', 'up-thin' => '\2191', 'ccw' => '\27f2', 'cw' => '\27f3', 'arrows-ccw' => '\1f504', 'level-down' => '\21b3', 'level-up' => '\21b0', 'shuffle' => '\1f500', 'loop' => '\1f501', 'switch' => '\21c6', 'play' => '\25b6', 'stop' => '\25a0', 'pause' => '\2389', 'record' => '\26ab', 'to-end' => '\23ed', 'to-start' => '\23ee', 'fast-forward' => '\23e9', 'fast-backward' => '\23ea', 'progress-0' => '\e768', 'progress-1' => '\e769', 'progress-2' => '\e76a', 'progress-3' => '\e76b', 'target' => '\1f3af', 'palette' => '\1f3a8', 'list' => '\e005', 'list-add' => '\e003', 'signal' => '\1f4f6', 'trophy' => '\1f3c6', 'battery' => '\1f50b', 'back-in-time' => '\e771', 'monitor' => '\1f4bb', 'mobile' => '\1f4f1', 'network' => '\e776', 'cd' => '\1f4bf', 'inbox' => '\e777', 'install' => '\e778', 'globe' => '\1f30e', 'cloud' => '\2601', 'cloud-thunder' => '\26c8', 'flash' => '\26a1', 'moon' => '\263d', 'flight' => '\2708', 'paper-plane' => '\e79b', 'leaf' => '\1f342', 'lifebuoy' => '\e788', 'mouse' => '\e789', 'briefcase' => '\1f4bc', 'suitcase' => '\e78e', 'dot' => '\e78b', 'dot-2' => '\e78c', 'dot-3' => '\e78d', 'brush' => '\e79a', 'magnet' => '\e7a1', 'infinity' => '\221e', 'erase' => '\232b', 'chart-pie' => '\e751', 'chart-line' => '\1f4c8', 'chart-bar' => '\1f4ca', 'chart-area' => '\1f53e', 'tape' => '\2707', 'graduation-cap' => '\1f393', 'language' => '\e752', 'ticket' => '\1f3ab', 'water' => '\1f4a6', 'droplet' => '\1f4a7', 'air' => '\e753', 'credit-card' => '\1f4b3', 'floppy' => '\1f4be', 'clipboard' => '\1f4cb', 'megaphone' => '\1f4e3', 'database' => '\e754', 'drive' => '\e755', 'bucket' => '\e756', 'thermometer' => '\e757', 'key' => '\1f511', 'flow-cascade' => '\e790', 'flow-branch' => '\e791', 'flow-tree' => '\e792', 'flow-line' => '\e793', 'flow-parallel' => '\e794', 'rocket' => '\1f680', 'gauge' => '\e7a2', 'traffic-cone' => '\e7a3', 'cc' => '\e7a5', 'cc-by' => '\e7a6', 'cc-nc' => '\e7a7', 'cc-nc-eu' => '\e7a8', 'cc-nc-jp' => '\e7a9', 'cc-sa' => '\e7aa', 'cc-nd' => '\e7ab', 'cc-pd' => '\e7ac', 'cc-zero' => '\e7ad', 'cc-share' => '\e7ae', 'cc-remix' => '\e7af', 'github' => '\f300', 'github-circled' => '\f301', 'flickr' => '\f303', 'flickr-circled' => '\f304', 'vimeo' => '\f306', 'vimeo-circled' => '\f307', 'twitter' => '\f309', 'twitter-circled' => '\f30a', 'facebook' => '\f30c', 'facebook-circled' => '\f30d', 'facebook-squared' => '\f30e', 'gplus' => '\f30f', 'gplus-circled' => '\f310', 'pinterest' => '\f312', 'pinterest-circled' => '\f313', 'tumblr' => '\f315', 'tumblr-circled' => '\f316', 'linkedin' => '\f318', 'linkedin-circled' => '\f319', 'dribbble' => '\f31b', 'dribbble-circled' => '\f31c', 'stumbleupon' => '\f31e', 'stumbleupon-circled' => '\f31f', 'lastfm' => '\f321', 'lastfm-circled' => '\f322', 'rdio' => '\f324', 'rdio-circled' => '\f325', 'spotify' => '\f327', 'spotify-circled' => '\f328', 'qq' => '\f32a', 'instagrem' => '\f32d', 'dropbox' => '\f330', 'evernote' => '\f333', 'flattr' => '\f336', 'skype' => '\f339', 'skype-circled' => '\f33a', 'renren' => '\f33c', 'sina-weibo' => '\f33f', 'paypal' => '\f342', 'picasa' => '\f345', 'soundcloud' => '\f348', 'mixi' => '\f34b', 'behance' => '\f34e', 'google-circles' => '\f351', 'vkontakte' => '\f354', 'smashing' => '\f357', 'sweden' => '\f601', 'db-shape' => '\f600', 'logo-db' => '\f603'];
$icons_complete = '';

if (count($icons) > 0) {
    ksort($icons);
    $list    = [];
    $classes = [];
    foreach ($icons as $class => $content) {
        $list[]     = '<li><span class="rex-icon rex-icon-help-' . $class . '"></span> ' . $class . ' :: ' . $content . '</li>';
        $classes[]  = '.rex-icon-help-' . $class . ':before{content:"' . $content . '"}';
    }

    $icons_complete = '<ul>' . implode('', $list) . '</ul>';
}
echo '
<style type="text/css">
    /*<![CDATA[*/
    ' . implode("\n", $classes) . '
    /*]]>*/
</style>

<div class="rex-grid2col">
    <div class="rex-column">
        ' . $icons_used . '
    </div>
    <div class="rex-column">
        ' . $icons_complete . '
    </div>
</div>';
