<?php

use Redaxo\Core\Content\Article;

/**
 * @extends rex_extension_point<string>
 */
class rex_extension_point_art_content_updated extends rex_extension_point
{
    public const NAME = 'ART_CONTENT_UPDATED';

    private Article $article;
    private string $action;

    /** @param array<string, mixed> $params */
    public function __construct(Article $article, string $action, string $subject = '', array $params = [], bool $readonly = false)
    {
        // for BC 'simple' attach params
        $params['article_id'] = $article->getId();
        $params['clang'] = $article->getClangId();

        parent::__construct(self::NAME, $subject, $params, $readonly);

        $this->article = $article;
        $this->action = $action;
    }

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}
