<?php

/**
 * @package redaxo5
 */

// *************************************** SUBPAGE: KATEGORIEN

$media_method = rex_request('media_method', 'string');
$csrf = rex_csrf_token::factory('mediapool_structure');

$edit_id = rex_request('edit_id', 'int');

try {
    if (in_array($media_method, ['edit_file_cat', 'delete_file_cat', 'add_file_cat'])) {
        if (!$csrf->isValid()) {
            $error = rex_i18n::msg('csrf_token_invalid');
        } else {
            if ('edit_file_cat' == $media_method) {
                $cat_name = rex_request('cat_name', 'string');
                $data = ['name' => $cat_name];
                $success = rex_media_category_service::editCategory($edit_id, $data);
            } elseif ('delete_file_cat' == $media_method) {
                try {
                    $success = rex_media_category_service::deleteCategory($edit_id);
                } catch (rex_functional_exception $e) {
                    $error = $e->getMessage();
                }
            } elseif ('add_file_cat' == $media_method) {
                $parent = null;
                $parentId = rex_request('cat_id', 'int');
                if ($parentId) {
                    $parent = rex_media_category::get($parentId);
                }
                $success = rex_media_category_service::addCategory(
                    rex_request('catname', 'string'),
                    $parent
                );
            }
        }
    }
} catch (rex_sql_exception $e) {
    $error = $e->getMessage();
}

$link = rex_url::currentBackendPage(array_merge($arg_url, ['cat_id' => '']));

$breadcrumb = [];

$n = [];
$n['title'] = rex_i18n::msg('pool_kat_start');
$n['href'] = $link . '0';
$breadcrumb[] = $n;

$cat_id = rex_request('cat_id', 'int');
if (0 == $cat_id || !($OOCat = rex_media_category::get($cat_id))) {
    $OOCats = rex_media_category::getRootCategories();
    $cat_id = 0;
    $catpath = '|';
} else {
    $OOCats = $OOCat->getChildren();
    $paths = explode('|', $OOCat->getPath());

    for ($i = 1; $i < count($paths); ++$i) {
        $iid = current($paths);
        if ('' != $iid) {
            $icat = rex_media_category::get($iid);

            $n = [];
            $n['title'] = rex_escape($icat->getName());
            $n['href'] = $link . $iid;
            $breadcrumb[] = $n;
        }
        next($paths);
    }
    $n = [];
    $n['title'] = rex_escape($OOCat->getName());
    $n['href'] = $link . $cat_id;
    $breadcrumb[] = $n;
    $catpath = $OOCat->getPath() . "$cat_id|";
}

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('pool_kat_path'), false);
$fragment->setVar('items', $breadcrumb, false);
echo $fragment->parse('core/navigations/breadcrumb.php');

if ('' != $error) {
    echo rex_view::error($error);
    $error = '';
}
if ('' != $success) {
    echo rex_view::info($success);
    $success = '';
}

if (!$PERMALL) {
    $addable = false;

    if ($cat_id > 0) {
        $pwp = media_category_perm_helper::checkParents(rex_media_category::get($cat_id), false);
        if (
            rex::getUser()->getComplexPerm('media')->hasCategoryPerm($cat_id) // check media cat
            || $pwp instanceof rex_media_category
        ) {
            $addable = true;
        }
    }
} else {
    $addable = true;
}

$add = ($addable) ? '<a href="' . $link . $cat_id . '&amp;media_method=add_cat"' . rex::getAccesskey(rex_i18n::msg('pool_kat_create'), 'add') . ' title="' . rex_i18n::msg('pool_kat_create') . '"><i class="rex-icon rex-icon-add-media-category"></i></a>' : '';

$table = '
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th class="rex-table-icon">' . $add . '</th>
                <th class="rex-table-id">' . rex_i18n::msg('id') . '</th>
                <th>' . rex_i18n::msg('pool_kat_name') . '</th>
                <th class="rex-table-action" colspan="2">' . rex_i18n::msg('pool_kat_function') . '</th>
            </tr>
        </thead>
        <tbody>';

if ('add_cat' == $media_method) {
    $table .= '
        <tr class="mark">
            <td class="rex-table-icon"><i class="rex-icon rex-icon-media-category"></i></td>
            <td class="rex-table-id" data-title="' . rex_i18n::msg('id') . '">-</td>
            <td data-title="' . rex_i18n::msg('pool_kat_name') . '"><input class="form-control" type="text" name="catname" value="" autofocus /></td>
            <td class="rex-table-action" colspan="2">
                <button class="btn btn-save" type="submit" value="' . rex_i18n::msg('pool_kat_create') . '"' . rex::getAccesskey(rex_i18n::msg('pool_kat_create'), 'save') . '>' . rex_i18n::msg('pool_kat_create') . '</button>
            </td>
        </tr>
    ';
}

foreach ($OOCats as $OOCat) {

    $childWithPermission = media_category_perm_helper::checkChildren($OOCat, false);
    $parentWithPermission = media_category_perm_helper::checkParents($OOCat, false);

    if (
        rex::getUser()->getComplexPerm('media')->hasCategoryPerm($OOCat->getId()) // check media cat
        || $parentWithPermission instanceof rex_media_category // check all parents
        || $childWithPermission instanceof rex_media_category // check children
    ) {

        $iid = $OOCat->getId();
        $iname = $OOCat->getName();

        $editable = (rex::getUser()->getComplexPerm('media')->hasAll() || $parentWithPermission); // || rex::getUser()->getComplexPerm('media')->hasCategoryPerm($OOCat->getId()));

        if ($media_method == 'update_file_cat' && $edit_id == $iid) {
            $table .= '
                <tr class="mark">
                    <td class="rex-table-icon"><i class="rex-icon rex-icon-media-category"></i></td>
                    <td class="rex-table-id" data-title="' . rex_i18n::msg('id') . '">' . $iid . '</td>
                    <td data-title="' . rex_i18n::msg('pool_kat_name') . '"><input class="form-control" type="text" name="cat_name" value="' . rex_escape($iname) . '" /></td>
                    <td class="rex-table-action" colspan="2">
                        <input type="hidden" name="edit_id" value="' . $edit_id . '" />
                        <button class="btn btn-save" type="submit" value="' . rex_i18n::msg('pool_kat_update') . '"' . rex::getAccesskey(rex_i18n::msg('pool_kat_update'), 'save') . '>' . rex_i18n::msg('pool_kat_update') . '</button>
                    </td>
                </tr>
            ';
        } else {

            $edit = ($editable) ? '<a href="' . $link . $cat_id . '&amp;media_method=update_file_cat&amp;edit_id=' . $iid . '"><i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('pool_kat_edit') . '</a>' : '';
            $delete = ($editable) ? '<a href="' . $link . $cat_id . '&amp;media_method=delete_file_cat&amp;edit_id=' . $iid . '&amp;' . http_build_query($csrf->getUrlParams()) . '" data-confirm="' . rex_i18n::msg('delete') . ' ?"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('pool_kat_delete') . '</a>' : '';

            $table .= '
                <tr>
                    <td class="rex-table-icon"><a href="' . $link . $iid . '" title="' . rex_escape($OOCat->getName()) . '"><i class="rex-icon rex-icon-media-category"></i></a></td>
                    <td class="rex-table-id" data-title="' . rex_i18n::msg('id') . '">' . $iid . '</td>
                    <td data-title="' . rex_i18n::msg('pool_kat_name') . '"><a href="' . $link . $iid . '">' . rex_escape($OOCat->getName()) . '</a></td>
                    <td class="rex-table-action">'.$edit.'</td>
                    <td class="rex-table-action">'.$delete.'</td>
                </tr>';
        }

    }
}
$table .= '
        </tbody>
    </table>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('pool_kat_caption'), false);
$fragment->setVar('content', $table, false);
$content = $fragment->parse('core/page/section.php');

if ('add_cat' == $media_method || 'update_file_cat' == $media_method) {
    $add_mode = 'add_cat' == $media_method;
    $method = $add_mode ? 'add_file_cat' : 'edit_file_cat';

    $content = '
        <form action="' . rex_url::currentBackendPage() . '" method="post">
            ' . $csrf->getHiddenField() . '
            <fieldset>
                <input type="hidden" name="media_method" value="' . $method . '" />
                <input type="hidden" name="cat_id" value="' . $cat_id . '" />
                <input type="hidden" name="catpath" value="' . $catpath . '" />
                ' . $arg_fields . '
                ' . $content . '
            </fieldset>
        </form>
    </div>
    ';
}

echo $content;
