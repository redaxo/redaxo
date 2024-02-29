<?php

/**
 * @package redaxo\core
 */
class rex_clang_service
{
    /**
     * Erstellt eine Clang.
     *
     * @param string $code Clang Code
     * @param string $name Name
     * @param int $priority Priority
     * @param bool $status Status
     * @return void
     */
    public static function addCLang($code, $name, $priority, $status = false)
    {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTablePrefix() . 'clang');
        $sql->setNewId('id');
        $sql->setValue('code', $code);
        $sql->setValue('name', $name);
        $sql->setValue('priority', $priority);
        $sql->setValue('status', $status);
        $sql->insert();
        $id = (int) $sql->getLastId();

        rex_sql_util::organizePriorities(rex::getTable('clang'), 'priority', '', 'priority, id != ' . $id);

        $firstLang = rex_sql::factory();
        $firstLang->setQuery('select * from ' . rex::getTablePrefix() . 'article where clang_id=?', [rex_clang::getStartId()]);
        $fields = $firstLang->getFieldnames();

        $newLang = rex_sql::factory();
        // $newLang->setDebug();
        foreach ($firstLang as $firstLangArt) {
            $newLang->setTable(rex::getTablePrefix() . 'article');

            foreach ($fields as $value) {
                if ('pid' == $value) {
                    continue;
                } // nix passiert
                if ('clang_id' == $value) {
                    $newLang->setValue('clang_id', $id);
                } elseif ('status' == $value) {
                    $newLang->setValue('status', '0');
                } // Alle neuen Artikel offline
                else {
                    $newLang->setValue($value, $firstLangArt->getValue($value));
                }
            }

            $newLang->insert();
        }

        rex_delete_cache();

        // ----- EXTENSION POINT
        $clang = rex_clang::get($id);
        rex_extension::registerPoint(new rex_extension_point('CLANG_ADDED', '', [
            'id' => $clang->getId(),
            'name' => $clang->getName(),
            'clang' => $clang,
        ]));
    }

    /**
     * Ändert eine Clang.
     *
     * @param int $id Id der Clang
     * @param string $code Clang Code
     * @param string $name Name der Clang
     * @param int $priority Priority
     * @param bool|null $status Status
     *
     * @throws rex_exception
     *
     * @return bool
     */
    public static function editCLang($id, $code, $name, $priority, $status = null)
    {
        if (!rex_clang::exists($id)) {
            throw new rex_exception('clang with id "' . $id . '" does not exist');
        }

        $oldPriority = rex_clang::get($id)->getPriority();

        $editLang = rex_sql::factory();
        $editLang->setTable(rex::getTablePrefix() . 'clang');
        $editLang->setWhere(['id' => $id]);
        $editLang->setValue('code', $code);
        $editLang->setValue('name', $name);
        $editLang->setValue('priority', $priority);
        if (null !== $status) {
            $editLang->setValue('status', $status);
        }
        $editLang->update();

        $comparator = $oldPriority < $priority ? '=' : '!=';
        rex_sql_util::organizePriorities(rex::getTable('clang'), 'priority', '', 'priority, id' . $comparator . $id);

        rex_delete_cache();

        // ----- EXTENSION POINT
        $clang = rex_clang::get($id);
        rex_extension::registerPoint(new rex_extension_point('CLANG_UPDATED', '', [
            'id' => $clang->getId(),
            'name' => $clang->getName(),
            'clang' => $clang,
        ]));

        return true;
    }

    /**
     * Löscht eine Clang.
     *
     * @param int $id Zu löschende ClangId
     *
     * @throws rex_exception
     * @return void
     */
    public static function deleteCLang($id)
    {
        $startClang = rex_clang::getStartId();
        if ($id == $startClang) {
            throw new rex_functional_exception(rex_i18n::msg('clang_error_startidcanotbedeleted', $startClang));
        }

        if (!rex_clang::exists($id)) {
            throw new rex_functional_exception(rex_i18n::msg('clang_error_idcanotbedeleted', $id));
        }

        $clang = rex_clang::get($id);

        $del = rex_sql::factory();
        $del->setQuery('delete from ' . rex::getTablePrefix() . 'clang where id=?', [$id]);

        rex_sql_util::organizePriorities(rex::getTable('clang'), 'priority', '', 'priority');

        $del->setQuery('delete from ' . rex::getTablePrefix() . 'article where clang_id=?', [$id]);
        $del->setQuery('delete from ' . rex::getTablePrefix() . 'article_slice where clang_id=?', [$id]);

        rex_delete_cache();

        // ----- EXTENSION POINT
        rex_extension::registerPoint(new rex_extension_point('CLANG_DELETED', '', [
            'id' => $clang->getId(),
            'name' => $clang->getName(),
            'clang' => $clang,
        ]));
    }

    /**
     * Schreibt Spracheigenschaften in die Datei include/clang.php.
     *
     * @throws rex_exception
     * @return void
     */
    public static function generateCache()
    {
        $lg = rex_sql::factory();
        $lg->setQuery('select * from ' . rex::getTablePrefix() . 'clang order by priority');

        $clangs = [];
        foreach ($lg as $lang) {
            $id = (int) $lang->getValue('id');
            foreach ($lg->getFieldnames() as $field) {
                $clangs[$id][$field] = $lang->getValue($field);
            }
        }

        $file = rex_path::coreCache('clang.cache');
        if (!rex_file::putCache($file, $clangs)) {
            throw new rex_exception('Clang cache file could not be generated');
        }
    }
}
