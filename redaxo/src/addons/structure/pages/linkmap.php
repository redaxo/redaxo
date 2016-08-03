<?php

// ------- Default Values

$opener_input_field = rex_request('opener_input_field', 'string');
$opener_input_field_name = rex_request('opener_input_field_name', 'string');
$category_id = rex_request('category_id', 'int');
$category_id = rex_category::get($category_id) ? $category_id : 0;
$clang = rex_request('clang', 'int');
$clang = rex_clang::exists($clang) ? $clang : rex_clang::getStartId();

$context = new rex_context([
    'page' => rex_be_controller::getCurrentPage(),
    'opener_input_field' => $opener_input_field,
    'opener_input_field_name' => $opener_input_field_name,
    'category_id' => $category_id,
    'clang' => $clang,
]);

// ------- Build JS Functions

$func_body = '';

if ($opener_input_field != '' && $opener_input_field_name == '') {
    $opener_input_field_name = $opener_input_field . '_NAME';
}
if (substr($opener_input_field, 0, 13) == 'REX_LINKLIST_') {
    $id = substr($opener_input_field, 13, strlen($opener_input_field));
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
if ("$opener_input_field".substring(0,9) == 'REX_LINK_') {
    window.opener.document.getElementById("$opener_input_field").value = link.replace("redaxo://","");
    window.opener.document.getElementById("$opener_input_field_name").value = name;
    self.close();
}
else {
    opener.jQuery(window).trigger("rex:selectLink", [link, name]);
    opener.jQuery('body').trigger("rex:selectLink", [link, name, window.name]);
}
return false;
JS;
}

// ------------------------ Print JS Functions

?>
<script type="text/javascript">
    function insertLink(link,name){
        <?php echo $func_body . "\n" ?>
    }
</script>

<?php

$isRoot = $category_id === 0;
$category = rex_category::get($category_id);

$navigation = [];
if ($category) {
    foreach ($category->getParentTree() as $parent) {
        $n = [];
        $n['title'] = str_replace(' ', '&nbsp;', htmlspecialchars($parent->getName()));
        $n['href'] = $context->getUrl(['category_id' => $parent->getId()]);
        $navigation[] = $n;
    }
}

echo rex_view::title('<i class="rex-icon rex-icon-linkmap"></i> Linkmap');

$title = '<a href="' . $context->getUrl(['category_id' => 0]) . '"><i class="rex-icon rex-icon-sitestartarticle"></i> ' . rex_i18n::msg('homepage') . '</a>';

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
