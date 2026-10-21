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

final class MediaCategoryHandler
{
    private function __construct() {}

    /**
     * @param string $name The name of the new category
     * @param MediaCategory|null $parent The category in which the new category should be created, or null for a top/root level category
     */
    public static function add(string $name, ?MediaCategory $parent): MediaCategory
    {
        $db = Sql::factory();

        $parentId = $parent?->id;
        $path = '|';
        if ($parent) {
            $path .= implode('|', $parent->path) . '|' . $parent->id . '|';
        }

        $db->setTable('rex_media_category');
        $db->setValue('name', $name);
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
     * @param int $categoryId The id of the category to edit
     * @param array{name: string} $data The category data
     *
     * @throws UserMessageException
     */
    public static function edit(int $categoryId, array $data): MediaCategory
    {
        $previous = MediaCategory::get($categoryId);
        if (!$previous) {
            throw new UserMessageException(I18n::msg('pool_kat_not_found'));
        }

        $catName = $data['name'];

        $db = Sql::factory();
        $db->setTable('rex_media_category');
        $db->setWhere(['id' => $categoryId]);
        $db->setValue('name', $catName);
        $db->addGlobalUpdateFields();

        $db->update();

        MediaPoolCache::deleteCategory($categoryId);

        $category = Type::notNull(MediaCategory::get($categoryId));

        Extension::dispatch(new MediaCategoryUpdated($category, $previous));

        return $category;
    }
}
