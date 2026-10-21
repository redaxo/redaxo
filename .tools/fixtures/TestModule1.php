<?php

namespace Redaxo\Core\Fixtures;

use Override;
use Redaxo\Core\Content\AsModule;
use Redaxo\Core\Content\Module;
use Redaxo\Core\Form\Field\Checkbox;
use Redaxo\Core\Form\Field\Choice;
use Redaxo\Core\Form\Field\Collection;
use Redaxo\Core\Form\Field\MediaPicker;
use Redaxo\Core\Form\Field\Text;
use Redaxo\Core\Form\Field\Textarea;
use Redaxo\Core\MediaPool\Media;

#[AsModule('testmodule1', 'Test Module 1')]
final class TestModule1 extends Module
{
    #[Text('Headline', required: true)]
    public string $headline = '';

    #[Textarea(
        'Text',
        rows: 3,
    )]
    public string $text = '';

    #[Choice(
        'Layout',
        choices: ['wide' => 'Wide', 'narrow' => 'Narrow'],
    )]
    public string $layout = 'wide';

    #[MediaPicker('Image')]
    public ?Media $image = null;

    #[Checkbox('Show caption')]
    public bool $caption = false;

    /** @var list<TestModuleTeaser> */
    #[Collection(
        'Teasers',
        item: TestModuleTeaser::class,
        max: 5,
    )]
    public array $teasers = [];

    #[Override]
    public function output(): string
    {
        return 'output';
    }
}
