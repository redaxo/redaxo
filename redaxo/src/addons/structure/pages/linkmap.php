<?php

// ------- Default Values

$opener_input_field = rex_request('opener_input_field', 'string');
$opener_input_field_name = rex_request('opener_input_field_name', 'string');
$category_id = rex_request('category_id', 'int');
$category_id = rex_category::get($category_id) ? $category_id : 0;
$clang = rex_request('clang', 'int');
$clang = rex_clang::exists($clang) ? $clang : rex_clang::getStartId();

$pattern = '/[^a-z0-9_-]/i';
if (preg_match($pattern, $opener_input_field, $match)) {
    throw new InvalidArgumentException(sprintf('Invalid character "%s" in opener_input_field.', $match[0]));
}
if (preg_match($pattern, $opener_input_field_name, $match)) {
    throw new InvalidArgumentException(sprintf('Invalid character "%s" in opener_input_field_name.', $match[0]));
}

$context = new rex_context([
    'page' => rex_be_controller::getCurrentPage(),
    'opener_input_field' => $opener_input_field,
    'opener_input_field_name' => $opener_input_field_name,
    'category_id' => $category_id,
    'clang' => $clang,
]);

// ------- Build JS Functions

$func_body = '';

if ('' != $opener_input_field && '' == $opener_input_field_name) {
    $opener_input_field_name = $opener_input_field . '_NAME';
}
if ('REX_LINKLIST_' == substr($opener_input_field, 0, 13)) {
    $id = (int) substr($opener_input_field, 13, strlen($opener_input_field));
    $func_body .= 'var linklist = "REX_LINKLIST_SELECT_' . $id . '";
                             var linkid = link.replace("redaxo://","");
                 var source = opener.document.getElementById(linklist);
                 var sourcelength = source.options.length;

                             option = opener.document.createElement("OPTION");
                             option.text = name;
                             option.value = linkid;

                 source.options.add(option, sourcelength);
                 opener.writeREXLinklist(' . $id . ');';
} else {
    $func_body .= <<<JS
var event = opener.jQuery.Event("rex:selectLink");
opener.jQuery(window).trigger(event, [link, name]);
if (!event.isDefaultPrevented()) {
    var linkid = link.replace("redaxo://","");
    window.opener.document.getElementById("$opener_input_field").value = linkid;
    window.opener.document.getElementById("$opener_input_field_name").value = name;
    self.close();
}
JS;
}

// ------------------------ Print JS Functions

$retainEventHandlers = '';
if (!rex_request::isXmlHttpRequest()) {
    $retainEventHandlers = 'rex_retain_popup_event_handlers("rex:selectLink");';
}

?>
<script type="text/javascript">
    <?php echo $retainEventHandlers ?>

    function insertLink(link,name){
        <?php echo $func_body . "\n" ?>
    }
</script>

<?php

$isRoot = 0 === $category_id;
$category = rex_category::get($category_id);

$navigation = [];
if ($category) {
    foreach ($category->getParentTree() as $parent) {
        $n = [];
        $n['title'] = str_replace(' ', '&nbsp;', rex_escape($parent->getName()));
        $n['href'] = $context->getUrl(['category_id' => $parent->getId()]);
        $navigation[] = $n;
    }
}

echo rex_view::title('<i class="rex-icon rex-icon-linkmap"></i> Linkmap');

$title = '<a href="' . $context->getUrl(['category_id' => 0]) . '"><i class="rex-icon rex-icon-structure-root-level"></i> ' . rex_i18n::msg('root_level') . '</a>';

$fragment = new rex_fragment();
$fragment->setVar('title', $title, false);
$fragment->setVar('items', $navigation, false);
echo $fragment->parse('core/navigations/breadcrumb.php');

$content = [];

$categoryTree = new rex_linkmap_category_tree($context);
$panel = $categoryTree->getTree($category_id);

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('linkmap_categories'), false);
$fragment->setVar('content', $panel, false);
$content[] = $fragment->parse('core/page/section.php');

$articleList = new rex_linkmap_article_list($context);
$panel = $articleList->getList($category_id);

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('linkmap_articles'), false);
$fragment->setVar('content', $panel, false);
$content[] = $fragment->parse('core/page/section.php');

$fragment = new rex_fragment();
$fragment->setVar('content', $content, false);
$fragment->setVar('classes', ['col-sm-6', 'col-sm-6'], false);
echo $fragment->parse('core/page/grid.php');
