<?php

namespace Redaxo\Core\MetaInfo\Handler;

use Redaxo\Core\Content\ArticleCache;
use Redaxo\Core\Content\Category;
use Redaxo\Core\Content\ExtensionPoint\CategoryAdded;
use Redaxo\Core\Content\ExtensionPoint\CategoryUpdated;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\ExtensionPoint\AsExtension;
use Redaxo\Core\ExtensionPoint\ExtensionLevel;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\Http\Request;
use Redaxo\Core\Language\Language;
use Redaxo\Core\MetaInfo\MetaContext;
use Redaxo\Core\MetaInfo\MetaEntity;

/**
 * @internal
 */
final class CategoryHandler extends AbstractHandler
{
    public const CONTAINER = 'rex-structure-category-metainfo';

    /** @param ExtensionPoint<string> $ep */
    #[AsExtension('CAT_FORM_BUTTONS')]
    public function renderToggleButton(ExtensionPoint $ep): string
    {
        $params = $ep->getParams();

        /** @var object|null $subject */
        $subject = $params['category'] ?? null;
        $category = isset($params['id']) ? Category::get((int) $params['id'], (int) $params['language']) : null;

        if ($this->hasFields(new MetaContext(MetaEntity::Category, $subject, $category))) {
            return $ep->subject . '<a class="btn btn-default collapsed" data-toggle="collapse" href="#' . self::CONTAINER . '"><i class="rex-icon rex-icon-structure-category-metainfo"></i></a>';
        }

        return $ep->subject;
    }

    /** @param ExtensionPoint<string> $ep */
    #[AsExtension('CAT_FORM_ADD')]
    #[AsExtension('CAT_FORM_EDIT')]
    public function extendForm(ExtensionPoint $ep): string
    {
        return $ep->subject . '
            <tr id="' . self::CONTAINER . '" class="collapse mark">
                <td colspan="2"></td>
                <td colspan="5">
                    <div class="rex-collapse-content">
                    ' . $this->renderFields($this->createContext($ep->getParams())) . '
                    </div>
                </td>
            </tr>';
    }

    /** Saves the submitted values together with the category, as only that request is protected by a csrf token. */
    #[AsExtension(level: ExtensionLevel::Early)]
    public function saveForm(CategoryAdded|CategoryUpdated $ep): void
    {
        if ('post' !== Request::requestMethod()) {
            return;
        }

        // the values of a freshly added category apply to all languages
        $languageIds = $ep instanceof CategoryAdded ? Language::getAllIds() : [$ep->category->languageId];

        $this->save($ep->category->id, $languageIds, new MetaContext(MetaEntity::Category, $ep->category, $ep->category));
    }

    /** @param array<string, mixed> $params */
    private function createContext(array $params): MetaContext
    {
        /** @var object|null $subject */
        $subject = $params['category'] ?? null;
        $languageId = isset($params['language']) ? (int) $params['language'] : null;
        // The surrounding category (the edited category, or the parent when adding); null = root.
        $category = isset($params['id']) ? Category::get((int) $params['id'], $languageId) : null;

        return new MetaContext(MetaEntity::Category, $subject, $category);
    }

    /** @param list<int> $languageIds */
    private function save(int $id, array $languageIds, MetaContext $context): void
    {
        foreach ($languageIds as $languageId) {
            $translation = Sql::factory();
            $translation->setTable('rex_category_translation');
            $translation->setWhere(['category_id' => $id, 'language_id' => $languageId]);
            $this->saveRequestValues($translation, $context, translatable: true);
            if ($translation->hasValues()) {
                $translation->update();
            }
        }

        $shared = Sql::factory();
        $shared->setTable('rex_category');
        $shared->setWhere(['id' => $id]);
        $this->saveRequestValues($shared, $context, translatable: false);
        $shared->addGlobalUpdateFields();
        $shared->update();

        // Regenerate the article with the additional values.
        ArticleCache::generateMeta($id);
    }
}
