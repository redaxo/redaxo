<?php

namespace Redaxo\Core\MetaInfo;

use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Form\Field\MediaPicker;
use Redaxo\Core\Translation\I18n;

use function implode;
use function Redaxo\Core\View\escape;

/**
 * Finds the media referenced by meta fields.
 *
 * @internal
 */
final class MediaUsage
{
    private function __construct() {}

    /**
     * Returns the warnings listing the items whose meta fields reference the given medium.
     *
     * @return list<string>
     */
    public static function getWarnings(string $filename): array
    {
        $warning = [];

        $sql = Sql::factory();
        $escapedFilename = $sql->escape($filename);

        $where = ['articles' => [], 'categories' => [], 'media' => [], 'languages' => []];
        $map = [
            [MetaEntity::Article, 'articles'],
            [MetaEntity::Category, 'categories'],
            [MetaEntity::Media, 'media'],
            [MetaEntity::Language, 'languages'],
        ];
        foreach ($map as [$entity, $key]) {
            foreach (MetaSchema::getFields($entity) as $field) {
                if ($field->definition->field instanceof MediaPicker) {
                    $where[$key][] = 'FIND_IN_SET(' . $escapedFilename . ', ' . $sql->escapeIdentifier($field->column) . ')';
                }
            }
        }

        $articles = '';
        if (!empty($where['articles'])) {
            $items = $sql->getArray('SELECT a.id, t.language_id, t.name FROM rex_article a JOIN rex_article_translation t ON t.article_id = a.id WHERE ' . implode(' OR ', $where['articles']));
            foreach ($items as $artArr) {
                $aid = (int) $artArr['id'];
                $languageId = (int) $artArr['language_id'];
                $articles .= '<li><a href="javascript:openPage(\'' . Url::backendPage('content', ['article_id' => $aid, 'mode' => 'meta', 'language' => $languageId]) . '\')">' . escape((string) $artArr['name']) . '</a></li>';
            }
            if ('' != $articles) {
                $warning[] = I18n::msg('minfo_media_in_use_art') . '<br /><ul>' . $articles . '</ul>';
            }
        }

        $categories = '';
        if (!empty($where['categories'])) {
            $items = $sql->getArray('SELECT c.id, ct.language_id, a.parent_id, ct.name FROM rex_category c JOIN rex_article a ON a.id = c.id JOIN rex_category_translation ct ON ct.category_id = c.id WHERE ' . implode(' OR ', $where['categories']));
            foreach ($items as $artArr) {
                $aid = (int) $artArr['id'];
                $languageId = (int) $artArr['language_id'];
                $parentId = (int) $artArr['parent_id'];
                $categories .= '<li><a href="javascript:openPage(\'' . Url::backendPage('structure', ['edit_id' => $aid, 'function' => 'edit_cat', 'category_id' => $parentId, 'language' => $languageId]) . '\')">' . escape((string) $artArr['name']) . '</a></li>';
            }
            if ('' != $categories) {
                $warning[] = I18n::msg('minfo_media_in_use_cat') . '<br /><ul>' . $categories . '</ul>';
            }
        }

        $media = '';
        if (!empty($where['media'])) {
            $items = $sql->getArray('SELECT DISTINCT m.id, m.filename, m.category_id FROM rex_media m LEFT JOIN rex_media_translation l ON l.media_id = m.id WHERE ' . implode(' OR ', $where['media']));
            foreach ($items as $medArr) {
                $id = (int) $medArr['id'];
                $filename = escape((string) $medArr['filename']);
                $catId = (int) $medArr['category_id'];
                $media .= '<li><a href="' . Url::backendPage('mediapool/detail', ['file_id' => $id, 'rex_file_category' => $catId]) . '">' . $filename . '</a></li>';
            }
            if ('' != $media) {
                $warning[] = I18n::msg('minfo_media_in_use_med') . '<br /><ul>' . $media . '</ul>';
            }
        }

        $languageList = '';
        if (!empty($where['languages'])) {
            $items = $sql->getArray('SELECT id, name FROM rex_language WHERE ' . implode(' OR ', $where['languages']));
            foreach ($items as $languageRow) {
                $name = escape((string) $languageRow['name']);
                if (Core::getUser()?->admin) {
                    $languageList .= '<li><a href="javascript:openPage(\'' . Url::backendPage('system/lang', ['language_id' => (int) $languageRow['id'], 'func' => 'edit']) . '\')">' . $name . '</a></li>';
                } else {
                    $languageList .= '<li>' . $name . '</li>';
                }
            }
            if ('' != $languageList) {
                $warning[] = I18n::msg('minfo_media_in_use_language') . '<br /><ul>' . $languageList . '</ul>';
            }
        }

        return $warning;
    }
}
