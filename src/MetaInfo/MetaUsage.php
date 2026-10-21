<?php

namespace Redaxo\Core\MetaInfo;

use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Form\Field\ArticlePicker;
use Redaxo\Core\Form\Field\MediaPicker;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\UsageList;

use function implode;
use function is_a;

/**
 * Finds the media and articles referenced by meta fields.
 *
 * @internal
 */
final class MetaUsage
{
    private function __construct() {}

    /** Adds the items whose meta fields reference the given medium. */
    public static function addMediaUsages(UsageList $usages, string $filename): void
    {
        self::addUsages($usages, MediaPicker::class, $filename);
    }

    /** Adds the items whose meta fields reference the given article, except the article itself and its category. */
    public static function addArticleUsages(UsageList $usages, int $articleId): void
    {
        self::addUsages($usages, ArticlePicker::class, (string) $articleId, $articleId);
    }

    /**
     * @param class-string<MediaPicker|ArticlePicker> $picker
     * @param int|null $articleId Article (and the category it is the start article of) to skip
     */
    private static function addUsages(UsageList $usages, string $picker, string $target, ?int $articleId = null): void
    {
        $sql = Sql::factory();

        $where = static function (MetaEntity $entity) use ($sql, $picker, $target): ?string {
            $conditions = [];
            foreach (MetaSchema::getFields($entity) as $field) {
                if (is_a($field->definition->field, $picker)) {
                    $conditions[] = 'FIND_IN_SET(' . $sql->escape($target) . ', ' . $sql->escapeIdentifier($field->column) . ')';
                }
            }

            return $conditions ? '(' . implode(' OR ', $conditions) . ')' : null;
        };

        $exclude = static fn (string $column): string => null === $articleId ? '' : ' AND ' . $column . ' != ' . $articleId;

        if ($condition = $where(MetaEntity::Article)) {
            $items = $sql->getArray('SELECT a.id, t.language_id, t.name FROM rex_article a JOIN rex_article_translation t ON t.article_id = a.id WHERE ' . $condition . $exclude('a.id'));
            foreach ($items as $item) {
                $usages->add(I18n::rawMsg('minfo_usage_articles'), (string) $item['name'], Url::backendPage('content', ['article_id' => (int) $item['id'], 'mode' => 'meta', 'language' => (int) $item['language_id']]));
            }
        }

        if ($condition = $where(MetaEntity::Category)) {
            $items = $sql->getArray('SELECT c.id, ct.language_id, a.parent_id, ct.name FROM rex_category c JOIN rex_article a ON a.id = c.id JOIN rex_category_translation ct ON ct.category_id = c.id WHERE ' . $condition . $exclude('c.id'));
            foreach ($items as $item) {
                $usages->add(I18n::rawMsg('minfo_usage_categories'), (string) $item['name'], Url::backendPage('structure', ['edit_id' => (int) $item['id'], 'function' => 'edit_cat', 'category_id' => (int) $item['parent_id'], 'language' => (int) $item['language_id']]));
            }
        }

        if ($condition = $where(MetaEntity::Media)) {
            $items = $sql->getArray('SELECT DISTINCT m.id, m.filename, m.category_id FROM rex_media m LEFT JOIN rex_media_translation l ON l.media_id = m.id WHERE ' . $condition);
            foreach ($items as $item) {
                $usages->add(I18n::rawMsg('minfo_usage_media'), (string) $item['filename'], Url::backendPage('mediapool/detail', ['file_id' => (int) $item['id'], 'rex_file_category' => (int) $item['category_id']]));
            }
        }

        if ($condition = $where(MetaEntity::Language)) {
            $admin = (bool) Core::getUser()?->admin;
            foreach ($sql->getArray('SELECT id, name FROM rex_language WHERE ' . $condition) as $item) {
                $usages->add(I18n::rawMsg('minfo_usage_languages'), (string) $item['name'], $admin ? Url::backendPage('system/lang', ['language_id' => (int) $item['id'], 'func' => 'edit']) : null);
            }
        }
    }
}
