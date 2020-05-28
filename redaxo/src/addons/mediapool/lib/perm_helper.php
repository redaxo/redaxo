<?php

/**
 * @package redaxo\mediapool
 */
class media_category_perm_helper
{
    /**
     * @param rex_media_category $mediacat
     * @return bool|mixed|rex_media_category
     */
    public static function checkChildren(rex_media_category $mediacat, $check_read_perms)
    {
        $children = $mediacat->getChildren();
        if (is_array($children)) {
            foreach ($children as $child) {

                // check child of child
                $childs = $child->getChildren();
                if (is_array($childs)) {
                    $matchedChild = self::checkChildren($child, $check_read_perms);
                }

                // return matched child
                if ($matchedChild instanceof rex_media_category) {
                    return $matchedChild;
                }

                // check child self
                if (rex::getUser()->getComplexPerm('media')->hasCategoryPerm($child->getId()) ||
                    ($check_read_perms && rex::getUser()->getComplexPerm('media_read')->hasCategoryPerm($child->getId()))
                ) {
                    return $child;
                } else {
                    continue;
                }

            }
        }
        return false;
    }

    /**
     * @param null|rex_media_category $mediacat
     * @param $check_read_perms
     * @return bool|rex_media_category
     */
    public static function checkParents($mediacat, $check_read_perms)
    {
        if ($mediacat instanceof rex_media_category && sizeof($mediacat->getPathAsArray()) > 0) {
            foreach ($mediacat->getPathAsArray() as $parent) {
                if (rex::getUser()->getComplexPerm('media')->hasCategoryPerm($parent) ||
                    ($check_read_perms && rex::getUser()->getComplexPerm('media_read')->hasCategoryPerm($parent))
                ) {
                    return rex_media_category::get($parent);
                }
            }
        }
        return false;
    }

    /**
     * @param rex_media_category $mediacat
     * @param $id
     * @return bool
     */
    public static function isIdParentInPath(rex_media_category $mediacat, $id)
    {
        return is_numeric(strpos($mediacat->getPath(),"|$id|"));
    }
}
