<?php

namespace Redaxo\Core\Fixtures;

use Redaxo\Core\Content\Article;
use Redaxo\Core\Form\Field\ArticlePicker;
use Redaxo\Core\Form\Field\Text;

final class TestModuleTeaser
{
    #[Text('Title', required: true)]
    public string $title = '';

    #[ArticlePicker('Link')]
    public ?Article $link = null;
}
