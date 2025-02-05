<?php

namespace Redaxo\Core\Language;

use Redaxo\Core\Cache;
use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Database\Util;
use Redaxo\Core\Exception\UserMessageException;
use Redaxo\Core\ExtensionPoint\Extension;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Translation\I18n;
use rex_exception;

class LanguageHandler
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
        $sql = Sql::factory();
        $sql->setTable(Core::getTablePrefix() . 'clang');
        $sql->setNewId('id');
        $sql->setValue('code', $code);
        $sql->setValue('name', $name);
        $sql->setValue('priority', $priority);
        $sql->setValue('status', $status);
        $sql->insert();
        $id = $sql->getLastId();

        Util::organizePriorities(Core::getTable('clang'), 'priority', '', 'priority, id != ' . $id);

        $firstLang = Sql::factory();
        $firstLang->setQuery('select * from ' . Core::getTablePrefix() . 'article where clang_id=?', [Language::getStartId()]);
        $fields = $firstLang->getFieldnames();

        $newLang = Sql::factory();
        // $newLang->setDebug();
        foreach ($firstLang as $firstLangArt) {
            $newLang->setTable(Core::getTablePrefix() . 'article');

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

        Cache::delete();

        // ----- EXTENSION POINT
        $clang = Language::get($id);
        Extension::registerPoint(new ExtensionPoint('CLANG_ADDED', '', [
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
        if (!Language::exists($id)) {
            throw new rex_exception('Language with id "' . $id . '" does not exist');
        }

        $oldPriority = Language::get($id)->getPriority();

        $editLang = Sql::factory();
        $editLang->setTable(Core::getTablePrefix() . 'clang');
        $editLang->setWhere(['id' => $id]);
        $editLang->setValue('code', $code);
        $editLang->setValue('name', $name);
        $editLang->setValue('priority', $priority);
        if (null !== $status) {
            $editLang->setValue('status', $status);
        }
        $editLang->update();

        $comparator = $oldPriority < $priority ? '=' : '!=';
        Util::organizePriorities(Core::getTable('clang'), 'priority', '', 'priority, id' . $comparator . $id);

        Cache::delete();

        // ----- EXTENSION POINT
        $clang = Language::get($id);
        Extension::registerPoint(new ExtensionPoint('CLANG_UPDATED', '', [
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
     * @throws UserMessageException
     * @return void
     */
    public static function deleteCLang($id)
    {
        $startClang = Language::getStartId();
        if ($id == $startClang) {
            throw new UserMessageException(I18n::msg('clang_error_startidcanotbedeleted', $startClang));
        }

        if (!Language::exists($id)) {
            throw new UserMessageException(I18n::msg('clang_error_idcanotbedeleted', $id));
        }

        $clang = Language::get($id);

        $del = Sql::factory();
        $del->setQuery('delete from ' . Core::getTablePrefix() . 'clang where id=?', [$id]);

        Util::organizePriorities(Core::getTable('clang'), 'priority', '', 'priority');

        $del->setQuery('delete from ' . Core::getTablePrefix() . 'article where clang_id=?', [$id]);
        $del->setQuery('delete from ' . Core::getTablePrefix() . 'article_slice where clang_id=?', [$id]);

        Cache::delete();

        // ----- EXTENSION POINT
        Extension::registerPoint(new ExtensionPoint('CLANG_DELETED', '', [
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
        $lg = Sql::factory();
        $lg->setQuery('select * from ' . Core::getTablePrefix() . 'clang order by priority');

        $clangs = [];
        foreach ($lg as $lang) {
            $id = (int) $lang->getValue('id');
            foreach ($lg->getFieldnames() as $field) {
                $clangs[$id][$field] = $lang->getValue($field);
            }
        }

        $file = Path::coreCache('clang.cache');
        if (!File::putCache($file, $clangs)) {
            throw new rex_exception('Language cache file could not be generated');
        }
    }
}
