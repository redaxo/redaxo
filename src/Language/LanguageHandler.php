<?php

namespace Redaxo\Core\Language;

use Redaxo\Core\Cache;
use Redaxo\Core\Database\DatabaseUtil;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Database\Table;
use Redaxo\Core\Exception\RuntimeException;
use Redaxo\Core\Exception\UserMessageException;
use Redaxo\Core\ExtensionPoint\Extension;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Language\ExtensionPoint\LanguageAdded;
use Redaxo\Core\Language\ExtensionPoint\LanguageDeleted;
use Redaxo\Core\Language\ExtensionPoint\LanguageUpdated;
use Redaxo\Core\MetaInfo\MetaEntity;
use Redaxo\Core\MetaInfo\MetaValues;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\Type;

use function array_key_exists;

final class LanguageHandler
{
    private function __construct() {}

    /**
     * Creates a language and copies the articles of an existing one into it.
     *
     * @param array{code: string, name: string, priority?: int, status?: bool} $data Language data, the language is
     *     appended if `priority` is missing
     * @param array<string, mixed> $meta meta values, keyed by the storage key of the field (see {@see MetaValues})
     */
    public static function add(array $data, array $meta = []): Language
    {
        $metaValues = new MetaValues(MetaEntity::Language, $meta);

        Type::requireKeys($data, 'code', 'name');

        $sql = Sql::factory();
        $sql->setTable('rex_language');
        $sql->setNewId('id');
        $sql->setValue('code', $data['code']);
        $sql->setValue('name', $data['name']);
        $sql->setValue('priority', $data['priority'] ?? Language::count() + 1);
        $sql->setValue('status', $data['status'] ?? false);
        $sql->setValues($metaValues->forTable('rex_language'));
        $sql->insert();
        $id = $sql->getLastId();

        DatabaseUtil::organizePriorities('rex_language', 'priority', '', 'priority, id != ' . $id);

        $sourceId = Language::getStartId();
        if ($sourceId === $id) {
            foreach (Language::getAllIds(true) as $languageId) {
                if ($sourceId !== $languageId) {
                    $sourceId = $languageId;
                    break;
                }
            }
        }

        // the translations of the source language are the starting point for the new language, articles start offline
        self::copyTranslations('rex_article_translation', $sourceId, $id, ['status' => 0]);
        self::copyTranslations('rex_category_translation', $sourceId, $id);
        self::copyTranslations('rex_media_translation', $sourceId, $id);

        Cache::delete();

        $language = Language::require($id);

        // ----- EXTENSION POINT
        Extension::dispatch(new LanguageAdded($language));

        return $language;
    }

    /**
     * Updates a language.
     *
     * @param array{code?: string, name?: string, priority?: int, status?: bool} $data Language data, missing keys keep
     *     their value
     * @param array<string, mixed> $meta meta values, keyed by the storage key of the field (see {@see MetaValues})
     *
     * @throws UserMessageException
     */
    public static function edit(int $id, array $data, array $meta = []): Language
    {
        $metaValues = new MetaValues(MetaEntity::Language, $meta);

        if (!Language::exists($id)) {
            throw new UserMessageException(I18n::msg('language_not_found', $id));
        }

        $previous = Language::require($id);
        $oldPriority = $previous->priority;

        $editLang = Sql::factory();
        $editLang->setTable('rex_language');
        $editLang->setWhere(['id' => $id]);
        foreach (['code', 'name', 'priority', 'status'] as $key) {
            if (array_key_exists($key, $data)) {
                $editLang->setValue($key, $data[$key]);
            }
        }
        $editLang->setValues($metaValues->forTable('rex_language'));
        if ($editLang->hasValues()) {
            $editLang->update();
        }

        if (isset($data['priority']) && $data['priority'] !== $oldPriority) {
            $comparator = $oldPriority < $data['priority'] ? '=' : '!=';
            DatabaseUtil::organizePriorities('rex_language', 'priority', '', 'priority, id' . $comparator . $id);
        }

        Cache::delete();

        $language = Language::require($id);

        // ----- EXTENSION POINT
        Extension::dispatch(new LanguageUpdated($language, $previous));

        return $language;
    }

    /**
     * Deletes a language together with its articles and slices.
     *
     * @throws UserMessageException
     */
    public static function delete(int $id): void
    {
        $startLanguageId = Language::getStartId();
        if ($id == $startLanguageId) {
            throw new UserMessageException(I18n::msg('language_error_startidcanotbedeleted', $startLanguageId));
        }

        if (!Language::exists($id)) {
            throw new UserMessageException(I18n::msg('language_not_found', $id));
        }

        $language = Language::require($id);

        Sql::factory()->setQuery('delete from rex_language where id=?', [$id]);

        DatabaseUtil::organizePriorities('rex_language', 'priority', '', 'priority');

        Cache::delete();

        // ----- EXTENSION POINT
        Extension::dispatch(new LanguageDeleted($language));
    }

    /**
     * Writes the language properties to the cache file.
     *
     * @return array<int, array<string, scalar|null>>
     */
    public static function generateCache(): array
    {
        $lg = Sql::factory();
        $lg->setQuery('select * from rex_language order by priority');

        $languages = [];
        foreach ($lg as $lang) {
            $id = $lang->getIntValue('id');
            foreach ($lg->getFieldnames() as $field) {
                $languages[$id][$field] = $lang->getValue($field);
            }
        }

        $file = Path::coreCache('language.cache');
        if (!File::putCache($file, $languages)) {
            throw new RuntimeException('Language cache file could not be generated');
        }

        return $languages;
    }

    /**
     * Copies all rows of a translation table from one language to another.
     *
     * @param non-empty-string $table
     * @param array<string, int|string> $overrides values replacing the copied ones, keyed by column
     */
    private static function copyTranslations(string $table, int $fromLanguageId, int $toLanguageId, array $overrides = []): void
    {
        $sql = Sql::factory();
        $overrides['language_id'] = $toLanguageId;

        $columns = array_keys(Table::get($table)->getColumns());
        $insertColumns = implode(', ', array_map($sql->escapeIdentifier(...), $columns));
        $selectColumns = implode(', ', array_map(
            static fn (string $column): string => isset($overrides[$column]) ? $sql->escape((string) $overrides[$column]) : $sql->escapeIdentifier($column),
            $columns,
        ));

        $sql->setQuery(
            'INSERT INTO ' . $sql->escapeIdentifier($table) . ' (' . $insertColumns . ') SELECT ' . $selectColumns . ' FROM ' . $sql->escapeIdentifier($table) . ' WHERE language_id = ?',
            [$fromLanguageId],
        );
    }
}
