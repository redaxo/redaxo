<?php

/**
 * @package redaxo\structure
 */
class rex_structure_context
{
    /**
     * @var array
     */
    private $params;

    /**
     * @param array $params
     */
    public function __construct(array $params)
    {
        if (!isset($params['category_id']) || !rex_category::get($params['category_id'])) {
            $params['category_id'] = 0;
        }
        // Only one mountpoint -> jump to category
        $mountpoints = $this->getMountpoints();
        if (1 == count($mountpoints) && 0 == $params['category_id']) {
            $params['category_id'] = current($mountpoints);
        }

        if (!isset($params['article_id']) || !rex_article::get($params['article_id'])) {
            $params['article_id'] = 0;
        }

        if (!isset($params['clang_id'])) {
            $params['clang_id'] = 0;
        }
        if (rex_clang::count() > 1 && !rex::getUser()->getComplexPerm('clang')->hasPerm($params['clang_id'])) {
            $params['clang_id'] = 0;
            foreach (rex_clang::getAllIds() as $key) {
                if (rex::getUser()->getComplexPerm('clang')->hasPerm($key)) {
                    $params['clang_id'] = $key;
                    break;
                }
            }
        } else {
            $params['clang_id'] = rex_clang::getStartId();
        }

        $this->params = $params;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->getValue('category_id', 0);
    }

    /**
     * @return int
     */
    public function getArticleId()
    {
        return $this->getValue('article_id', 0);
    }

    /**
     * @return int
     */
    public function getClangId()
    {
        return $this->getValue('clang_id', 0);
    }

    /**
     * @return int
     */
    public function getCtypeId()
    {
        return $this->getValue('ctype_id', 0);
    }

    /**
     * @return int
     */
    public function getArtStart()
    {
        return $this->getValue('artstart', 0);
    }

    /**
     * @return int
     */
    public function getCatStart()
    {
        return $this->getValue('catstart', 0);
    }

    /**
     * @return int
     */
    public function getEditId()
    {
        return $this->getValue('edit_id', 0);
    }

    /**
     * @return string
     */
    public function getFunction()
    {
        return $this->getValue('function', '');
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
    public function hasCategoryPermission()
    {
        return rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($this->getCategoryId());
    }

    /**
     * @return int
     */
    public function getRowsPerPage()
    {
        return $this->getValue('rows_per_page', 30);
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

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function getValue($key, $default)
    {
        return isset($this->params[$key]) ? $this->params[$key] : $default;
    }
}
