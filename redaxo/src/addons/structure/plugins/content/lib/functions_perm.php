<?php
/**
 * @package redaxo\structure\content
 */
class rex_functions_perm extends rex_complex_perm
{
    public function hasPerm()
    {
        if ($this->hasAll()) {
            return true;
        }

        if (
            rex::getUser()->hasPerm('article2category[]') ||
            rex::getUser()->hasPerm('article2startarticle[]') ||
            rex::getUser()->hasPerm('copyArticle[]') ||
            rex::getUser()->hasPerm('moveArticle[]') ||
            rex::getUser()->hasPerm('moveCategory[]') ||
            rex::getUser()->hasPerm('copyContent[]')
        ) {
            return true;
        }

        return false;
    }
}
