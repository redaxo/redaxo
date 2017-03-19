<?php

/**
 * @package redaxo\structure\content
 */
class rex_content_service
{
    /**
     * Verschiebt einen Slice.
     *
     * @param int    $slice_id  Id des Slices
     * @param int    $clang     Id der Sprache
     * @param string $direction Richtung in die verschoben werden soll
     *
     * @throws rex_exception
     * @throws rex_api_exception
     *
     * @return string Eine Statusmeldung
     */
    public static function moveSlice($slice_id, $clang, $direction)
    {
        // ctype beachten
        // verschieben / vertauschen
        // article regenerieren.

        // check if slice id is valid
        $CM = rex_sql::factory();
        $CM->setQuery('select * from ' . rex::getTablePrefix() . 'article_slice where id=? and clang_id=?', [$slice_id, $clang]);
        if ($CM->getRows() == 1) {
            // origin value for later success-check
            $oldPriority = $CM->getValue('priority');

            // prepare sql for later saving
            $upd = rex_sql::factory();
            $upd->setTable(rex::getTablePrefix() . 'article_slice');
            $upd->setWhere([
                'id' => $slice_id,
            ]);

            // some vars for later use
            $article_id = $CM->getValue('article_id');
            $ctype = $CM->getValue('ctype_id');
            $slice_revision = $CM->getValue('revision');

            rex_extension::registerPoint(new rex_extension_point('SLICE_MOVE', '', [
                'direction' => $direction,
                'slice_id' => $slice_id,
                'article_id' => $article_id,
                'clang_id' => $clang,
                'slice_revision' => $slice_revision,
            ]));

            if ($direction == 'moveup' || $direction == 'movedown') {
                if ($direction == 'moveup') {
                    $upd->setValue('priority', $CM->getValue('priority') - 1);
                    $updSort = 'DESC';
                } elseif ($direction == 'movedown') {
                    $upd->setValue('priority', $CM->getValue('priority') + 1);
                    $updSort = 'ASC';
                }
                $upd->addGlobalUpdateFields(self::getUser());
                $upd->update();

                rex_sql_util::organizePriorities(
                    rex::getTable('article_slice'),
                    'priority',
                    'article_id=' . (int) $article_id . ' AND clang_id=' . (int) $clang . ' AND ctype_id=' . (int) $ctype . ' AND revision=' . (int) $slice_revision,
                    'priority, updatedate ' . $updSort
                );

                // check if the slice moved at all (first cannot be moved up, last not down)
                $CM->setQuery('select * from ' . rex::getTablePrefix() . 'article_slice where id=? and clang_id=?', [$slice_id, $clang]);
                $newPriority = $CM->getValue('priority');
                if ($oldPriority == $newPriority) {
                    throw new rex_api_exception(rex_i18n::msg('slice_moved_error'));
                }

                rex_article_cache::deleteContent($article_id, $clang);
            } else {
                throw new rex_exception('rex_moveSlice: Unsupported direction "' . $direction . '"!');
            }
        } else {
            throw new rex_api_exception(rex_i18n::msg('slice_moved_error'));
        }

        return rex_i18n::msg('slice_moved');
    }

    /**
     * Löscht einen Slice.
     *
     * @param int $slice_id Id des Slices
     *
     * @return bool TRUE bei Erfolg, sonst FALSE
     */
    public static function deleteSlice($slice_id)
    {
        // check if slice id is valid
        $curr = rex_sql::factory();
        $curr->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article_slice WHERE id=?', [$slice_id]);
        if ($curr->getRows() != 1) {
            return false;
        }

        rex_extension::registerPoint(new rex_extension_point('SLICE_DELETE', '', [
            'slice_id' => $slice_id,
            'article_id' => $curr->getValue('article_id'),
            'clang_id' => $curr->getValue('clang_id'),
            'slice_revision' => $curr->getValue('revision'),
        ]));

        // delete the slice
        $del = rex_sql::factory();
        $del->setQuery('DELETE FROM ' . rex::getTablePrefix() . 'article_slice WHERE id=?', [$slice_id]);

        // reorg remaining slices
        rex_sql_util::organizePriorities(
            rex::getTable('article_slice'),
            'priority',
            'article_id=' . $curr->getValue('article_id') . ' AND clang_id=' . $curr->getValue('clang_id') . ' AND ctype_id=' . $curr->getValue('ctype_id') . ' AND revision=' . $curr->getValue('revision'),
            'priority'
        );

        // check if delete was successfull
        return $curr->getRows() == 1;
    }

    /**
     * Kopiert die Inhalte eines Artikels in einen anderen Artikel.
     *
     * @param int $from_id    ArtikelId des Artikels, aus dem kopiert werden (Quell ArtikelId)
     * @param int $to_id      ArtikelId des Artikel, in den kopiert werden sollen (Ziel ArtikelId)
     * @param int $from_clang ClangId des Artikels, aus dem kopiert werden soll (Quell ClangId)
     * @param int $to_clang   ClangId des Artikels, in den kopiert werden soll (Ziel ClangId)
     * @param int $revision
     *
     * @return bool TRUE bei Erfolg, sonst FALSE
     */
    public static function copyContent($from_id, $to_id, $from_clang = 1, $to_clang = 1, $revision = 0)
    {
        if ($from_id == $to_id && $from_clang == $to_clang) {
            return false;
        }

        $gc = rex_sql::factory();
        $gc->setQuery('select * from ' . rex::getTablePrefix() . 'article_slice where article_id=? and clang_id=? and revision=?', [$from_id, $from_clang, $revision]);

        if (!$gc->getRows()) {
            return true;
        }

        rex_extension::registerPoint(new rex_extension_point('ART_SLICES_COPY', '', [
            'article_id' => $to_id,
            'clang_id' => $to_clang,
            'slice_revision' => $revision,
        ]));

        $ins = rex_sql::factory();
        //$ins->setDebug();
        $ctypes = [];

        $cols = rex_sql::factory();
        //$cols->setDebug();
        $cols->setQuery('SHOW COLUMNS FROM ' . rex::getTablePrefix() . 'article_slice');

        $maxPriority = rex_sql::factory()->getArray(
            'SELECT `ctype_id`, MAX(`priority`) as max FROM ' . rex::getTable('article_slice') . ' WHERE `article_id` = :to_id AND `clang_id` = :to_clang AND `revision` = :revision GROUP BY `ctype_id`',
            ['to_id' => $to_id, 'to_clang' => $to_clang, 'revision' => $revision]
        );
        $maxPriority = array_column($maxPriority, 'max', 'ctype_id');

        $user = self::getUser();

        foreach ($gc as $slice) {
            foreach ($cols as $col) {
                $colname = $col->getValue('Field');
                if ($colname == 'clang_id') {
                    $value = $to_clang;
                } elseif ($colname == 'article_id') {
                    $value = $to_id;
                } elseif ($colname == 'priority') {
                    $ctypeId = $slice->getValue('ctype_id');
                    $value = $slice->getValue($colname) + (isset($maxPriority[$ctypeId]) ? $maxPriority[$ctypeId] : 0);
                } else {
                    $value = $slice->getValue($colname);
                }

                // collect all affected ctypes
                if ($colname == 'ctype_id') {
                    $ctypes[$value] = $value;
                }

                if ($colname != 'id') {
                    $ins->setValue($colname, $value);
                }
            }

            $ins->addGlobalUpdateFields($user);
            $ins->addGlobalCreateFields($user);
            $ins->setTable(rex::getTablePrefix() . 'article_slice');
            $ins->insert();
        }

        foreach ($ctypes as $ctype) {
            // reorg slices
            rex_sql_util::organizePriorities(
                rex::getTable('article_slice'),
                'priority',
                'article_id=' . (int) $to_id . ' AND clang_id=' . (int) $to_clang . ' AND ctype_id=' . (int) $ctype . ' AND revision=' . (int) $revision,
                'priority, updatedate'
            );
        }

        rex_article_cache::deleteContent($to_id, $to_clang);

        return true;
    }

    /**
     * Generiert den Artikel-Cache des Artikelinhalts.
     *
     * @param int $article_id Id des zu generierenden Artikels
     * @param int $clang      ClangId des Artikels
     *
     * @return bool TRUE bei Erfolg, FALSE wenn eine ungütlige article_id übergeben wird, sonst eine Fehlermeldung
     */
    public static function generateArticleContent($article_id, $clang = null)
    {
        foreach (rex_clang::getAllIds() as $_clang) {
            if ($clang !== null && $clang != $_clang) {
                continue;
            }

            $CONT = new rex_article_content_base();
            $CONT->setCLang($_clang);
            $CONT->setEval(false); // Content nicht ausführen, damit in Cachedatei gespeichert werden kann
            if (!$CONT->setArticleId($article_id)) {
                return false;
            }

            // --------------------------------------------------- Artikelcontent speichern
            $article_content_file = rex_path::addonCache('structure', "$article_id.$_clang.content");
            $article_content = $CONT->getArticle();

            // ----- EXTENSION POINT
            $article_content = rex_extension::registerPoint(new rex_extension_point('GENERATE_FILTER', $article_content, [
                'id' => $article_id,
                'clang' => $_clang,
                'article' => $CONT,
            ]));

            if (rex_file::put($article_content_file, $article_content) === false) {
                return rex_i18n::msg('article_could_not_be_generated') . ' ' . rex_i18n::msg('check_rights_in_directory') . rex_path::addonCache('structure');
            }
        }

        return true;
    }

    private static function getUser()
    {
        if (rex::getUser()) {
            return rex::getUser()->getLogin();
        }

        if (method_exists(rex::class, 'getEnvironment')) {
            return rex::getEnvironment();
        }

        return 'frontend';
    }
}
