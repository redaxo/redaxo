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
#[AsApiFunction('category_add')]
final class CategoryAdd extends ApiFunction
{
    public function execute(): Result
    {
        $user = Core::requireUser();
        if (!$user->hasPerm('addCategory[]')) {
            throw new ApiFunctionException('User has no permission to add categories!');
        }

        $parentId = Request::request('parent-category-id', 'int');

        if (0 !== $parentId && null === Category::get($parentId)) {
            throw new ApiFunctionException('Unable to find category with id "' . $parentId . '"!');
        }

        if (!$user->getComplexPerm('structure')->hasCategoryPerm($parentId)) {
            throw new ApiFunctionException(I18n::msg('no_rights_to_this_function'));
        }

        // prepare and validate parameters
        $data = [];
        $data['parent_id'] = $parentId ?: null;
        $data['priority'] = Request::post('category-position', 'int');
        $data['name'] = Request::post('category-name', 'string');
        $meta = new MetaForm(new MetaContext(MetaEntity::Category, category: Category::get($parentId)));
        if (!$meta->submit()) {
            throw new UserMessageException(I18n::msg('minfo_metadata_invalid'));
        }

        CategoryHandler::add($data, $meta->values());

        return new Result(true, I18n::msg('category_added_and_startarticle_created'));
    }
}
