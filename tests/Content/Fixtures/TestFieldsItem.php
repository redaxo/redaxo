<?php

namespace Redaxo\Core\Tests\Content\Fixtures;

use Redaxo\Core\Content\Article;
use Redaxo\Core\Form\Field\ArticlePicker;
use Redaxo\Core\Form\Field\Text;

final class TestFieldsItem
{
    #[Text('Title')]
    public string $title = '';

    #[ArticlePicker('Link')]
    public ?Article $link = null;
}
