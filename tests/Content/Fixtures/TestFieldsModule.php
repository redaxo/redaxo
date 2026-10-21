<?php

namespace Redaxo\Core\Tests\Content\Fixtures;

use Override;
use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\AsModule;
use Redaxo\Core\Content\Module;
use Redaxo\Core\Form\Field\ArticlePicker;
use Redaxo\Core\Form\Field\Checkbox;
use Redaxo\Core\Form\Field\MediaPicker;
use Redaxo\Core\Form\Field\Number;
use Redaxo\Core\Form\Field\Text;
use Redaxo\Core\MediaPool\Media;

#[AsModule('test_fields', 'Test Fields')]
final class TestFieldsModule extends Module
{
    #[Text('Headline', required: true)]
    public string $headline = '';

    #[Number('Item count')]
    public ?int $itemCount = null;

    #[Checkbox(
        'Highlight',
        key: 'hl',
    )]
    public bool $highlight = false;

    #[MediaPicker('Image')]
    public ?Media $image = null;

    /** @var list<Media> */
    #[MediaPicker(
        'Gallery',
        multiple: true,
    )]
    public array $gallery = [];

    #[ArticlePicker('Link')]
    public ?Article $link = null;

    #[Override]
    public function output(): string
    {
        return $this->headline . '|' . ($this->itemCount ?? '') . '|' . ($this->highlight ? 'hl' : '');
    }

    #[Override]
    public function validate(): ?string
    {
        return 'reject' === $this->headline ? 'Rejected' : null;
    }
}
