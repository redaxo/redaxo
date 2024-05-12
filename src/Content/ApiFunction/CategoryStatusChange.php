<?php

namespace Redaxo\Core\Content\ApiFunction;

use Redaxo\Core\ApiFunction\ApiFunction;
use Redaxo\Core\ApiFunction\Exception\ApiFunctionException;
use Redaxo\Core\ApiFunction\Result;
use Redaxo\Core\Content\CategoryHandler;
use Redaxo\Core\Core;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class CategoryStatusChange extends ApiFunction
{
    public function execute()
    {
        $categoryId = rex_request('category-id', 'int');
        $clang = rex_request('clang', 'int');
        $status = rex_request('cat_status', 'int', null);
        $user = Core::requireUser();

        // Check permissions
        if ($user->getComplexPerm('structure')->hasCategoryPerm($categoryId) && $user->hasPerm('publishCategory[]')) {
            CategoryHandler::categoryStatus($categoryId, $clang, $status);
            return new Result(true, I18n::msg('category_status_updated'));
        }

        throw new ApiFunctionException('User has no permission for this category!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
