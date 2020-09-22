<?php

/**
 * @package redaxo\mediapool
 */
class rex_media_category_perm_helper
{
    /**
     * @param rex_media_category $mediacat
     * @param bool $check_read_perms
     *
     * @return false|mixed|rex_media_category
     */
    public static function getMediaCategoryChildren(rex_media_category $mediacat, bool $check_read_perms)
    {
        $children = $mediacat->getChildren();
        if (is_array($children)) {
            foreach ($children as $child) {
                $matchedChild = null;
                // check child of child
                if (is_array($child->getChildren())) {
                    $matchedChild = self::getMediaCategoryChildren($child, $check_read_perms);
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
                }
                continue;
            }
        }
        return false;
    }

    /**
     * @param null|rex_media_category $mediacat
     * @param bool $check_read_perms
     *
     * @return false|rex_media_category|null
     */
    public static function getMediaCategoryParent($mediacat, bool $check_read_perms)
    {
        if ($mediacat instanceof rex_media_category && count($mediacat->getPathAsArray()) > 0) {
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
     * @param int $id
     *
     * @return bool
     */
    public static function isIdParentInPath(rex_media_category $mediacat, $id)
    {
        return in_array($id, $mediacat->getPathAsArray());
    }
}
