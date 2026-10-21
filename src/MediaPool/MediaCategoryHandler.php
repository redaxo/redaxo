<?php

namespace Redaxo\Core\MediaPool;

use Redaxo\Core\Database\Sql;
use Redaxo\Core\Exception\UserMessageException;
use Redaxo\Core\ExtensionPoint\Extension;
use Redaxo\Core\MediaPool\ExtensionPoint\MediaCategoryAdded;
use Redaxo\Core\MediaPool\ExtensionPoint\MediaCategoryDeleted;
use Redaxo\Core\MediaPool\ExtensionPoint\MediaCategoryUpdated;
use Redaxo\Core\MediaPool\ExtensionPoint\MediaCategoryUsageCheck;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\Type;
use Redaxo\Core\Util\UsageList;

use function array_key_exists;

final class MediaCategoryHandler
{
    private function __construct() {}

    /**
     * @param array{parent_id: int|null, name: string} $data Category data, `parent_id` is `null` for the root level
     *
     * @throws UserMessageException
     */
    public static function add(array $data): MediaCategory
    {
        Type::requireKeys($data, 'parent_id', 'name');

        $parentId = $data['parent_id'];
        $path = '|';
        if (null !== $parentId) {
            $parent = MediaCategory::get($parentId);
            if (!$parent) {
                throw new UserMessageException(I18n::msg('pool_kat_not_found'));
            }
            $path = '|' . implode('|', [...$parent->path, $parent->id]) . '|';
        }

        $db = Sql::factory();
        $db->setTable('rex_media_category');
        $db->setValue('name', $data['name']);
        $db->setValue('parent_id', $parentId);
        $db->setValue('path', $path);
        $db->addGlobalCreateFields();
        $db->addGlobalUpdateFields();

        $db->insert();
        $id = $db->getLastId();

        MediaPoolCache::deleteCategoryList($parentId);

        $category = Type::notNull(MediaCategory::get($id));

        Extension::dispatch(new MediaCategoryAdded($category));

        return $category;
    }

    /** @throws UserMessageException */
    public static function delete(int $categoryId): void
    {
        $category = MediaCategory::get($categoryId);
        if (!$category) {
            throw new UserMessageException(I18n::msg('pool_kat_not_found'));
        }

        $gf = Sql::factory();
        $gf->setQuery('SELECT * FROM rex_media WHERE category_id=?', [$categoryId]);
        $gd = Sql::factory();
        $gd->setQuery('SELECT * FROM rex_media_category WHERE parent_id=?', [$categoryId]);
        if (0 == $gf->getRows() && 0 == $gd->getRows()) {
            $usages = self::findUsages($category);
            if (!$usages->isEmpty()) {
                $name = "{$category->name} [$categoryId]";
                throw new UserMessageException('<strong>' . I18n::msg('pool_kat_delete_error', $name) . ' ' . I18n::msg('pool_object_in_use_by') . '</strong>' . $usages->render());
            }

            $gf->setQuery('DELETE FROM rex_media_category WHERE id=?', [$categoryId]);
            MediaPoolCache::deleteCategory($categoryId);
            MediaPoolCache::deleteLists();
        } else {
            throw new UserMessageException(I18n::msg('pool_kat_not_deleted'));
        }

        Extension::dispatch(new MediaCategoryDeleted($category));
    }

    /** Returns the places using the media category. */
    public static function findUsages(MediaCategory $category): UsageList
    {
        $usages = new UsageList();
        Extension::dispatch(new MediaCategoryUsageCheck($category, $usages));

        return $usages;
    }

    /**
     * @param array{name?: string} $data Category data, missing keys keep their value
     *
     * @throws UserMessageException
     */
    public static function edit(int $categoryId, array $data): MediaCategory
    {
        $previous = MediaCategory::get($categoryId);
        if (!$previous) {
            throw new UserMessageException(I18n::msg('pool_kat_not_found'));
        }

        $db = Sql::factory();
        $db->setTable('rex_media_category');
        $db->setWhere(['id' => $categoryId]);
        if (array_key_exists('name', $data)) {
            $db->setValue('name', $data['name']);
        }
        $db->addGlobalUpdateFields();

        $db->update();

        MediaPoolCache::deleteCategory($categoryId);

        $category = Type::notNull(MediaCategory::get($categoryId));

        Extension::dispatch(new MediaCategoryUpdated($category, $previous));

        return $category;
    }
}
