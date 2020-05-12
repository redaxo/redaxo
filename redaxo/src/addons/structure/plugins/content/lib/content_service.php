<?php

/**
 * @package redaxo\structure\content
 */
class rex_content_service
{
    /**
     * @throws rex_api_exception
     */
    public static function addSlice(int $articleId, int $clangId, int $ctypeId, int $moduleId, array $data = []): string
    {
        $data['revision'] = $data['revision'] ?? 0;

        $where = 'article_id=' . $articleId . ' AND clang_id=' . $clangId . ' AND ctype_id=' . $ctypeId . ' AND revision=' . (int) $data['revision'];

        if (!isset($data['priority'])) {
            $prevSlice = rex_sql::factory();
            $prevSlice->setQuery('SELECT IFNULL(MAX(priority),0)+1 as priority FROM ' . rex::getTable('article_slice') . ' WHERE '.$where);

            $data['priority'] = $prevSlice->getValue('priority');
        } elseif ($data['priority'] <= 0) {
            $data['priority'] = 1;
        }

        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('article_slice'));
        $sql->setValue('article_id', $articleId);
        $sql->setValue('clang_id', $clangId);
        $sql->setValue('ctype_id', $ctypeId);
        $sql->setValue('module_id', $moduleId);

        foreach ($data as $key => $value) {
            $sql->setValue($key, $value);
        }

        $sql->addGlobalCreateFields();
        $sql->addGlobalUpdateFields();

        try {
            $sql->insert();
            $sliceId = $sql->getLastId();

            rex_sql_util::organizePriorities(
                rex::getTable('article_slice'),
                'priority',
                $where,
                'priority, updatedate DESC'
            );
        } catch (rex_sql_exception $e) {
            throw new rex_api_exception($e->getMessage(), $e);
        }

        rex_article_cache::delete($articleId, $clangId);

        $message = rex_i18n::msg('slice_added');

        $article = rex_article::get($articleId, $clangId);

        // ----- EXTENSION POINT
        $message = rex_extension::registerPoint(new rex_extension_point('SLICE_ADDED', $message, [
            'article_id' => $articleId,
            'clang' => $clangId,
            'function' => '',
            'slice_id' => $sliceId,
            'page' => rex_be_controller::getCurrentPage(),
            'ctype' => $ctypeId,
            'category_id' => $article->getCategoryId(),
            'module_id' => $moduleId,
            'article_revision' => 0,
            'slice_revision' => $data['revision'],
        ]));

        $message = rex_extension::registerPoint(new rex_extension_point_art_content_updated($article, 'slice_added', $message));

        return $message;
    }

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
        if (1 == $CM->getRows()) {
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

            if ('moveup' == $direction || 'movedown' == $direction) {
                if ('moveup' == $direction) {
                    $upd->setValue('priority', $CM->getValue('priority') - 1);
                    $updSort = 'DESC';
                } else {
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

                $info = rex_i18n::msg('slice_moved');
                $article = rex_article::get($article_id, $clang);
                $info = rex_extension::registerPoint(new rex_extension_point_art_content_updated($article, 'slice_moved', $info));
            } else {
                throw new rex_exception('rex_moveSlice: Unsupported direction "' . $direction . '"!');
            }
        } else {
            throw new rex_api_exception(rex_i18n::msg('slice_moved_error'));
        }

        return $info;
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
        if (1 != $curr->getRows()) {
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
        return 1 == $curr->getRows();
    }

    public static function sliceStatus(int $sliceId, int $status)
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT article_id, clang_id FROM '.rex::getTable('article_slice').' WHERE id = ?', [$sliceId]);

        if (!$sql->getRows()) {
            throw new rex_exception(sprintf('Slice with id=%d not found.', $sliceId));
        }

        $article = rex_article::get($sql->getValue('article_id'), $sql->getValue('clang_id'));

        $sql->setTable(rex::getTable('article_slice'));
        $sql->setWhere(['id' => $sliceId]);
        $sql->setValue('status', $status);
        $sql->update();

        rex_article_cache::deleteContent($article->getId(), $article->getClangId());

        rex_extension::registerPoint(new rex_extension_point_art_content_updated($article, 'slice_status'));
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
                if ('clang_id' == $colname) {
                    $value = $to_clang;
                } elseif ('article_id' == $colname) {
                    $value = $to_id;
                } elseif ('priority' == $colname) {
                    $ctypeId = $slice->getValue('ctype_id');
                    $value = $slice->getValue($colname) + ($maxPriority[$ctypeId] ?? 0);
                } else {
                    $value = $slice->getValue($colname);
                }

                // collect all affected ctypes
                if ('ctype_id' == $colname) {
                    $ctypes[$value] = $value;
                }

                if ('id' != $colname) {
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

        $article = rex_article::get($to_id, $to_clang);
        rex_extension::registerPoint(new rex_extension_point_art_content_updated($article, 'content_copied'));

        return true;
    }

    /**
     * Generiert den Artikel-Cache des Artikelinhalts.
     *
     * @param int $article_id Id des zu generierenden Artikels
     * @param int $clang      ClangId des Artikels
     *
     * @throws rex_exception
     *
     * @return true
     */
    public static function generateArticleContent($article_id, $clang = null)
    {
        foreach (rex_clang::getAllIds() as $_clang) {
            if (null !== $clang && $clang != $_clang) {
                continue;
            }

            $CONT = new rex_article_content_base();
            $CONT->setCLang($_clang);
            $CONT->setEval(false); // Content nicht ausführen, damit in Cachedatei gespeichert werden kann
            if (!$CONT->setArticleId($article_id)) {
                throw new rex_exception(sprintf('Article %d does not exist.', $article_id));
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

            if (false === rex_file::put($article_content_file, $article_content)) {
                throw new rex_exception(sprintf('Article %d could not be generated, check the directory permissions for "%s".', $article_id, rex_path::addonCache('structure')));
            }
        }

        return true;
    }

    /**
     * @return string
     */
    private static function getUser()
    {
        if (rex::getUser()) {
            return rex::getUser()->getLogin();
        }

        return rex::getEnvironment();
    }
}
