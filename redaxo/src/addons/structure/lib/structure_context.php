<?php
/**
 * @package redaxo5
 */

class rex_structure_context
{
    /**
     * @var array
     */
    protected $global_params;

    /**
     * @param array $global_params
     */
    public function __construct(array $global_params = [])
    {
        if (isset($global_params['category_id'])) {
            $global_params['category_id'] = rex_category::get($global_params['category_id']) instanceof rex_category ? $global_params['category_id'] : 0;

            // Nur ein Mointpoint -> Sprung in die Kategory
            $mountpoints = $this->getMountpoints();
            if (count($mountpoints) == 1 && $global_params['category_id'] == 0) {
                $global_params['category_id'] = current($mountpoints);
            }
        }

        if (isset($global_params['article_id'])) {
            $global_params['article_id'] = rex_article::get($global_params['article_id']) instanceof rex_article ? $global_params['article_id'] : 0;
        }

        if (isset($global_params['clang_id'])) {
            $global_params['clang_id'] = rex_clang::exists($global_params['clang_id']) ? $global_params['clang_id'] : rex_clang::getStartId();

            $stop = false;
            if (rex_clang::count() > 1) {
                if (!rex::getUser()->getComplexPerm('clang')->hasPerm($global_params['clang_id'])) {
                    $stop = true;
                    foreach (rex_clang::getAllIds() as $key) {
                        if (rex::getUser()->getComplexPerm('clang')->hasPerm($key)) {
                            $global_params['clang_id'] = $key;
                            $stop = false;
                            break;
                        }
                    }

                    if ($stop) {
                        echo rex_view::error('You have no permission to this area');
                        exit;
                    }
                }
            } else {
                $global_params['clang_id'] = rex_clang::getStartId();
            }
        }

        $this->global_params = $global_params;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return isset($this->global_params['category_id']) ? (int) $this->global_params['category_id'] : 0;
    }

    /**
     * @return int
     */
    public function getArticleId()
    {
        return isset($this->global_params['article_id']) ? (int) $this->global_params['article_id'] : 0;
    }

    /**
     * @return int
     */
    public function getClangId()
    {
        return isset($this->global_params['clang_id']) ? (int) $this->global_params['clang_id'] : 0;
    }

    /**
     * @return int
     */
    public function getCtypeId()
    {
        return isset($this->global_params['ctype_id']) ? (int) $this->global_params['ctype_id'] : 0;
    }

    /**
     * @return int
     */
    public function getArtStart()
    {
        return isset($this->global_params['artstart']) ? (int) $this->global_params['artstart'] : 0;
    }

    /**
     * @return int
     */
    public function getCatStart()
    {
        return isset($this->global_params['catstart']) ? (int) $this->global_params['catstart'] : 0;
    }

    /**
     * @return int
     */
    public function getEditId()
    {
        return isset($this->global_params['edit_id']) ? (int) $this->global_params['edit_id'] : 0;
    }

    /**
     * @return string
     */
    public function getFunction()
    {
        return isset($this->global_params['function']) ? $this->global_params['function'] : '';
    }

    /**
     * @return array
     */
    public function getMountpoints()
    {
        return rex::getUser()->getComplexPerm('structure')->getMountpoints();
    }

    /**
     * @return bool
     */
    public function getCatPerm()
    {
        return rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($this->getCategoryId());
    }

    /**
     * @return rex_context
     */
    public function getContext()
    {
        return new rex_context([
            'page' => 'structure',
            'category_id' => $this->getCategoryId(),
            'article_id' => $this->getArticleId(),
            'clang' => $this->getClangId(),
        ]);
    }
}
