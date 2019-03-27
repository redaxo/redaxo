<?php

/**
 * @package redaxo\metainfo
 *
 * @internal
 */
class rex_metainfo_media_handler extends rex_metainfo_handler
{
    const PREFIX = 'med_';

    /**
     * Extension to check whether the given media is still in use.
     *
     * @param rex_extension_point $ep
     *
     * @throws rex_exception
     *
     * @return string
     */
    public static function isMediaInUse(rex_extension_point $ep)
    {
        $params = $ep->getParams();
        $warning = $ep->getSubject();

        $sql = rex_sql::factory();
        $sql->setQuery('SELECT `name`, `type_id` FROM `' . rex::getTablePrefix() . 'metainfo_field` WHERE `type_id` IN(6,7)');

        $rows = $sql->getRows();
        if ($rows == 0) {
            return $warning;
        }

        $where = [
            'articles' => [],
            'media' => [],
            'clangs' => [],
        ];
        $escapedFilename = $sql->escape($params['filename']);
        for ($i = 0; $i < $rows; ++$i) {
            $name = $sql->getValue('name');
            $prefix = rex_metainfo_meta_prefix($name);
            if (self::PREFIX === $prefix) {
                $key = 'media';
            } elseif (rex_metainfo_clang_handler::PREFIX === $prefix) {
                $key = 'clangs';
            } else {
                $key = 'articles';
            }
            switch ($sql->getValue('type_id')) {
                case '6':
                    $where[$key][] = $sql->escapeIdentifier($name) . ' = ' . $escapedFilename;
                    break;
                case '7':
                    $where[$key][] = 'FIND_IN_SET(' . $escapedFilename . ', ' . $sql->escapeIdentifier($name)  . ')';
                    break;
                default:
                    throw new rex_exception('Unexpected fieldtype "' . $sql->getValue('type_id') . '"!');
            }
            $sql->next();
        }

        $articles = '';
        $categories = '';
        if (!empty($where['articles'])) {
            $sql->setQuery('SELECT id, clang_id, parent_id, name, catname, startarticle FROM ' . rex::getTablePrefix() . 'article WHERE ' . implode(' OR ', $where['articles']));
            if ($sql->getRows() > 0) {
                foreach ($sql->getArray() as $art_arr) {
                    $aid = $art_arr['id'];
                    $clang = $art_arr['clang_id'];
                    $parent_id = $art_arr['parent_id'];
                    if ($art_arr['startarticle']) {
                        $categories .= '<li><a href="javascript:openPage(\'' . rex_url::backendPage('structure', ['edit_id' => $aid, 'function' => 'edit_cat', 'category_id' => $parent_id, 'clang' => $clang]) . '\')">' . $art_arr['catname'] . '</a></li>';
                    } else {
                        $articles .= '<li><a href="javascript:openPage(\'' . rex_url::backendPage('content', ['article_id' => $aid, 'mode' => 'meta', 'clang' => $clang]) . '\')">' . $art_arr['name'] . '</a></li>';
                    }
                }
                if ($articles != '') {
                    $warning[] = rex_i18n::msg('minfo_media_in_use_art') . '<br /><ul>' . $articles . '</ul>';
                }
                if ($categories != '') {
                    $warning[] = rex_i18n::msg('minfo_media_in_use_cat') . '<br /><ul>' . $categories . '</ul>';
                }
            }
        }

        $media = '';
        if (!empty($where['media'])) {
            $sql->setQuery('SELECT id, filename, category_id FROM ' . rex::getTablePrefix() . 'media WHERE ' . implode(' OR ', $where['media']));
            if ($sql->getRows() > 0) {
                foreach ($sql->getArray() as $med_arr) {
                    $id = $med_arr['id'];
                    $filename = $med_arr['filename'];
                    $cat_id = $med_arr['category_id'];
                    $media .= '<li><a href="' . rex_url::backendPage('mediapool/detail', ['file_id' => $id, 'rex_file_category' => $cat_id]) . '">' . $filename . '</a></li>';
                }
                if ($media != '') {
                    $warning[] = rex_i18n::msg('minfo_media_in_use_med') . '<br /><ul>' . $media . '</ul>';
                }
            }
        }

        $clangs = '';
        if (!empty($where['clangs'])) {
            $sql->setQuery('SELECT id, name FROM ' . rex::getTablePrefix() . 'clang WHERE ' . implode(' OR ', $where['clangs']));
            if ($sql->getRows() > 0) {
                foreach ($sql->getArray() as $clang_arr) {
                    if (rex::getUser() && rex::getUser()->isAdmin()) {
                        $clangs .= '<li><a href="javascript:openPage(\'' . rex_url::backendPage('system/lang', ['clang_id' => $clang_arr['id'], 'func' => 'editclang']) . '\')">' . $clang_arr['name'] . '</a></li>';
                    } else {
                        $clangs .= '<li>' . $clang_arr['name'] . '</li>';
                    }
                }
                if ($clangs != '') {
                    $warning[] = rex_i18n::msg('minfo_media_in_use_clang') . '<br /><ul>' . $clangs . '</ul>';
                }
            }
        }

        return $warning;
    }

    protected function buildFilterCondition(array $params)
    {
        $restrictionsCondition = '';

        $catId = rex_session('media[rex_file_category]', 'int');
        if (isset($params['activeItem'])) {
            $catId = $params['activeItem']->getValue('category_id');
        }

        if ($catId !== '') {
            $s = '';
            if ($catId != 0) {
                $OOCat = rex_media_category::get($catId);

                // Alle Metafelder des Pfades sind erlaubt
                foreach ($OOCat->getPathAsArray() as $pathElement) {
                    if ($pathElement != '') {
                        $s .= ' OR `p`.`restrictions` LIKE "%|' . $pathElement . '|%"';
                    }
                }
            }

            // Auch die Kategorie selbst kann Metafelder haben
            $s .= ' OR `p`.`restrictions` LIKE "%|' . $catId . '|%"';

            $restrictionsCondition = 'AND (`p`.`restrictions` = "" OR `p`.`restrictions` IS NULL ' . $s . ')';
        }

        return $restrictionsCondition;
    }

    protected function handleSave(array $params, rex_sql $sqlFields)
    {
        if (rex_request_method() != 'post' || !isset($params['id'])) {
            return $params;
        }

        $media = rex_sql::factory();
        //  $media->setDebug();
        $media->setTable(rex::getTablePrefix() . 'media');
        $media->setWhere('id=:mediaid', ['mediaid' => $params['id']]);

        parent::fetchRequestValues($params, $media, $sqlFields);

        // do the save only when metafields are defined
        if ($media->hasValues()) {
            $media->update();
        }

        return $params;
    }

    protected function renderFormItem($field, $tag, $tag_attr, $id, $label, $labelIt, $typeLabel)
    {
        return $field;
    }

    public function extendForm(rex_extension_point $ep)
    {
        $params = $ep->getParams();
        // Nur beim EDIT gibts auch ein Medium zum bearbeiten
        if ($ep->getName() == 'MEDIA_FORM_EDIT') {
            $params['activeItem'] = $params['media'];
            unset($params['media']);
        } elseif ($ep->getName() == 'MEDIA_ADDED') {
            $sql = rex_sql::factory();
            $qry = 'SELECT id FROM ' . rex::getTablePrefix() . 'media WHERE filename="' . $params['filename'] . '"';
            $sql->setQuery($qry);
            if ($sql->getRows() == 1) {
                $params['id'] = $sql->getValue('id');
            } else {
                throw new rex_exception('Error occured during file upload!');
            }
        }

        return $ep->getSubject() . parent::renderFormAndSave(self::PREFIX, $params);
    }
}

$mediaHandler = new rex_metainfo_media_handler();

rex_extension::register('MEDIA_FORM_EDIT', [$mediaHandler, 'extendForm']);
rex_extension::register('MEDIA_FORM_ADD', [$mediaHandler, 'extendForm']);

rex_extension::register('MEDIA_ADDED', [$mediaHandler, 'extendForm']);
rex_extension::register('MEDIA_UPDATED', [$mediaHandler, 'extendForm']);

rex_extension::register('MEDIA_IS_IN_USE', ['rex_metainfo_media_handler', 'isMediaInUse']);
