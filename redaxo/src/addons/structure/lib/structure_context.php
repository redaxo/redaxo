<?php

/**
 * @package redaxo\structure
 *
 * @internal
 */
class rex_structure_context
{
    /** @var array */
    private $params;

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
        if (rex_clang::count() > 1 && !rex::requireUser()->getComplexPerm('clang')->hasPerm($params['clang_id'])) {
            $params['clang_id'] = 0;
            foreach (rex_clang::getAllIds() as $key) {
                if (rex::requireUser()->getComplexPerm('clang')->hasPerm($key)) {
                    $params['clang_id'] = $key;
                    break;
                }
            }
        } elseif (!$params['clang_id']) {
            $params['clang_id'] = rex_clang::getStartId();
        }

        $this->params = $params;
    }

    public function getCategoryId(): int
    {
        return (int) $this->getValue('category_id', 0);
    }

    public function getArticleId(): int
    {
        return (int) $this->getValue('article_id', 0);
    }

    public function getClangId(): int
    {
        return (int) $this->getValue('clang_id', 0);
    }

    public function getCtypeId(): int
    {
        return (int) $this->getValue('ctype_id', 0);
    }

    public function getArtStart(): int
    {
        return (int) $this->getValue('artstart', 0);
    }

    public function getCatStart(): int
    {
        return (int) $this->getValue('catstart', 0);
    }

    public function getEditId(): int
    {
        return (int) $this->getValue('edit_id', 0);
    }

    public function getFunction(): string
    {
        return (string) $this->getValue('function', '');
    }

    public function getMountpoints(): array
    {
        return rex::requireUser()->getComplexPerm('structure')->getMountpoints();
    }

    public function hasCategoryPermission(): bool
    {
        return rex::requireUser()->getComplexPerm('structure')->hasCategoryPerm($this->getCategoryId());
    }

    public function getRowsPerPage(): int
    {
        return (int) $this->getValue('rows_per_page', 30);
    }

    public function getContext(): rex_context
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
        return $this->params[$key] ?? $default;
    }
}
