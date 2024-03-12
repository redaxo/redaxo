<?php

/**
 * @package redaxo\structure\content
 *
 * @extends rex_extension_point<string>
 */
class rex_extension_point_art_content_updated extends rex_extension_point
{
    public const NAME = 'ART_CONTENT_UPDATED';

    private rex_article $article;
    private string $action;

    /** @param array<string, mixed> $params */
    public function __construct(rex_article $article, string $action, string $subject = '', array $params = [], bool $readonly = false)
    {
        // for BC 'simple' attach params
        $params['article_id'] = $article->getId();
        $params['clang'] = $article->getClangId();

        parent::__construct(self::NAME, $subject, $params, $readonly);

        $this->article = $article;
        $this->action = $action;
    }

    public function getArticle(): rex_article
    {
        return $this->article;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}
