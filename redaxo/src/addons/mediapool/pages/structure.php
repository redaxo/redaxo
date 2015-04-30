<?php

/**
 *
 * @package redaxo5
 */

// *************************************** SUBPAGE: KATEGORIEN

$media_method = rex_request('media_method', 'string');

if ($PERMALL) {
    $edit_id = rex_request('edit_id', 'int');

    try {
        if ($media_method == 'edit_file_cat') {
            $cat_name = rex_request('cat_name', 'string');
            $db = rex_sql::factory();
            $db->setTable(rex::getTablePrefix() . 'media_category');
            $db->setWhere(['id' => $edit_id]);
            $db->setValue('name', $cat_name);
            $db->addGlobalUpdateFields();

            $db->update();
            $success = rex_i18n::msg('pool_kat_updated', $cat_name);
            rex_media_cache::deleteCategory($edit_id);

        } elseif ($media_method == 'delete_file_cat') {
            $gf = rex_sql::factory();
            $gf->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media WHERE category_id=' . $edit_id);
            $gd = rex_sql::factory();
            $gd->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media_category WHERE parent_id=' . $edit_id);
            if ($gf->getRows() == 0 && $gd->getRows() == 0) {
                $gf->setQuery('DELETE FROM ' . rex::getTablePrefix() . 'media_category WHERE id=' . $edit_id);
                rex_media_cache::deleteCategory($edit_id);
                rex_media_cache::deleteLists();
                $success = rex_i18n::msg('pool_kat_deleted');
            } else {
                $error = rex_i18n::msg('pool_kat_not_deleted');
            }
        } elseif ($media_method == 'add_file_cat') {
            $db = rex_sql::factory();
            $db->setTable(rex::getTablePrefix() . 'media_category');
            $db->setValue('name', rex_request('catname', 'string'));
            $db->setValue('parent_id', rex_request('cat_id', 'int'));
            $db->setValue('path', rex_request('catpath', 'string'));
            $db->addGlobalCreateFields();
            $db->addGlobalUpdateFields();

            $db->insert();
            $success = rex_i18n::msg('pool_kat_saved', rex_request('catname'));
            rex_media_cache::deleteCategoryList(rex_request('cat_id', 'int'));
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
    if ($cat_id == 0 || !($OOCat = rex_media_category::get($cat_id))) {
        $OOCats = rex_media_category::getRootCategories();
        $cat_id = 0;
        $catpath = '|';
    } else {
        $OOCats = $OOCat->getChildren();
        $paths = explode('|', $OOCat->getPath());

        for ($i = 1; $i < count($paths); $i++) {
            $iid = current($paths);
            if ($iid != '') {
                $icat = rex_media_category::get($iid);

                $n = [];
                $n['title'] = $icat->getName();
                $n['href'] = $link . $iid;
                $breadcrumb[] = $n;
            }
            next($paths);
        }
        $n = [];
        $n['title'] = $OOCat->getName();
        $n['href'] = $link . $cat_id;
        $breadcrumb[] = $n;
        $catpath = $OOCat->getPath() . "$cat_id|";
    }

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('pool_kat_path'), false);
    $fragment->setVar('items', $breadcrumb, false);
    echo $fragment->parse('core/navigations/breadcrumb.php');

    
    if ($error != '') {
        echo rex_view::error($error);
        $error = '';
    }
    if ($success != '') {
        echo rex_view::info($success);
        $success = '';
    }


    $table = '
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th><a href="' . $link . $cat_id . '&amp;media_method=add_cat"' . rex::getAccesskey(rex_i18n::msg('pool_kat_create'), 'add') . ' title="' . rex_i18n::msg('pool_kat_create') . '"><i class="rex-icon rex-icon-add-media-category"></i></a></th>
                    <th>' . rex_i18n::msg('id') . '</th>
                    <th>' . rex_i18n::msg('pool_kat_name') . '</th>
                    <th colspan="2">' . rex_i18n::msg('pool_kat_function') . '</th>
                </tr>
            </thead>
            <tbody>';

    if ($media_method == 'add_cat') {
        $table .= '
            <tr class="mark">
                <td><i class="rex-icon rex-icon-media-category"></i></td>
                <td>-</td>
                <td><input class="form-control" type="text" name="catname" value="" /></td>
                <td colspan="2">
                    <button class="btn btn-save" type="submit" value="' . rex_i18n::msg('pool_kat_create') . '"' . rex::getAccesskey(rex_i18n::msg('pool_kat_create'), 'save') . '>' . rex_i18n::msg('pool_kat_create') . '</button>
                </td>
            </tr>
        ';
    }

    foreach ( $OOCats as $OOCat) {

        $iid = $OOCat->getId();
        $iname = $OOCat->getName();

        if ($media_method == 'update_file_cat' && $edit_id == $iid) {
            $table .= '
                <tr class="mark">
                    <td><i class="rex-icon rex-icon-media-category"></i></td>
                    <td>' . $iid . '</td>
                    <td><input class="form-control" type="text" name="cat_name" value="' . htmlspecialchars($iname) . '" /></td>
                    <td colspan="2">
                        <input type="hidden" name="edit_id" value="' . $edit_id . '" />
                        <button class="btn btn-save" type="submit" value="' . rex_i18n::msg('pool_kat_update') . '"' . rex::getAccesskey(rex_i18n::msg('pool_kat_update'), 'save') . '>' . rex_i18n::msg('pool_kat_update') . '</button>
                    </td>
                </tr>
            ';
        } else {
            $table .= '
                <tr>
                    <td><a href="' . $link . $iid . '" title="' . htmlspecialchars($OOCat->getName()) . '"><i class="rex-icon rex-icon-media-category"></i></a></td>
                    <td>' . $iid . '</td>
                    <td>' . htmlspecialchars($OOCat->getName()) . '</td>
                    <td><a href="' . $link . $cat_id . '&amp;media_method=update_file_cat&amp;edit_id=' . $iid . '"><i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('pool_kat_edit') . '</a></td>
                    <td><a href="' . $link . $cat_id . '&amp;media_method=delete_file_cat&amp;edit_id=' . $iid . '" data-confirm="' . rex_i18n::msg('delete') . ' ?"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('pool_kat_delete') . '</a></td>
                </tr>';
}
    }
    $table .= '
            </tbody>
        </table>';



    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('pool_kat_caption'), false);
    $fragment->setVar('content', $table, false);
    $content = $fragment->parse('core/page/section.php');

    if ($media_method == 'add_cat' || $media_method == 'update_file_cat') {
        $add_mode = $media_method == 'add_cat';
        $method = $add_mode ? 'add_file_cat' : 'edit_file_cat';



        $content = '
            <form action="' . rex_url::currentBackendPage() . '" method="post">
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
}
