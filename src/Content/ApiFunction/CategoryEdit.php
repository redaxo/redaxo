<?php

namespace Redaxo\Core\Content\ApiFunction;

use Redaxo\Core\ApiFunction\ApiFunction;
use Redaxo\Core\ApiFunction\AsApiFunction;
use Redaxo\Core\ApiFunction\Exception\ApiFunctionException;
use Redaxo\Core\ApiFunction\Result;
use Redaxo\Core\Content\Category;
use Redaxo\Core\Content\CategoryHandler;
use Redaxo\Core\Core;
use Redaxo\Core\Exception\UserMessageException;
use Redaxo\Core\Http\Request;
use Redaxo\Core\MetaInfo\MetaContext;
use Redaxo\Core\MetaInfo\MetaEntity;
use Redaxo\Core\MetaInfo\MetaForm;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
#[AsApiFunction('category_edit')]
final class CategoryEdit extends ApiFunction
{
    public function execute(): Result
    {
        $user = Core::requireUser();
        if (!$user->hasPerm('editCategory[]')) {
            throw new ApiFunctionException('User has no permission to edit categories!');
        }

        $catId = Request::request('category-id', 'int');
        $languageId = Request::request('language', 'int');

        $category = Category::get($catId, $languageId);
        if (null === $category) {
            throw new ApiFunctionException('Unable to find category with id "' . $catId . '" and language "' . $languageId . '"!');
        }

        if (
            !$user->getComplexPerm('language')->hasPerm($languageId)
            || !$user->getComplexPerm('structure')->hasCategoryPerm($catId)
        ) {
            throw new ApiFunctionException(I18n::msg('no_rights_to_this_function'));
        }

        // prepare and validate parameters
        $data = [];
        $data['catpriority'] = Request::post('category-position', 'int');
        $data['catname'] = Request::post('category-name', 'string');
        $meta = new MetaForm(new MetaContext(MetaEntity::Category, $category, $category));
        if (!$meta->submit()) {
            throw new UserMessageException(I18n::msg('minfo_metadata_invalid'));
        }

        CategoryHandler::edit($catId, $languageId, $data, $meta->values());

        return new Result(true, I18n::msg('category_updated'));
    }
}
