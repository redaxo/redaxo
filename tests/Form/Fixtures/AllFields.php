<?php

namespace Redaxo\Core\Tests\Form\Fixtures;

use DateTimeImmutable;
use Redaxo\Core\Content\Article;
use Redaxo\Core\Form\Field\ArticlePicker;
use Redaxo\Core\Form\Field\Choice;
use Redaxo\Core\Form\Field\DatePicker;
use Redaxo\Core\Form\Field\DateTimePicker;
use Redaxo\Core\Form\Field\MediaPicker;
use Redaxo\Core\Form\Field\Number;
use Redaxo\Core\Form\Field\Textarea;
use Redaxo\Core\Form\Field\TimePicker;
use Redaxo\Core\MediaPool\Media;

/** @internal */
final class AllFields
{
    #[Textarea('Text', rows: 3)]
    public string $text = '';

    #[Number('Count', min: 1, max: 10)]
    public int $count = 1;

    #[Number('Price', step: 0.01)]
    public ?float $price = null;

    #[DatePicker('Date')]
    public ?DateTimeImmutable $date = null;

    #[DateTimePicker('Date and time')]
    public ?string $dateTime = null;

    #[TimePicker('Time')]
    public ?DateTimeImmutable $time = null;

    /** @var list<string> */
    #[Choice('Tags', choices: ['a' => 'A', 'Group' => ['b' => 'B', 'c' => 'C']], multiple: true)]
    public array $tags = [];

    /** @var list<int> */
    #[Choice('Days', choices: [1 => 'Mon', 2 => 'Tue', 3 => 'Wed'], multiple: true, expanded: true)]
    public array $days = [];

    #[Choice('Color', choices: self::colors(...), expanded: true)]
    public ?Color $color = null;

    #[ArticlePicker('Article')]
    public ?Article $article = null;

    /** @var list<Article> */
    #[ArticlePicker('Articles', multiple: true)]
    public array $articles = [];

    /** @var list<Media> */
    #[MediaPicker('Media', multiple: true, types: ['jpg'])]
    public array $media = [];

    /** @return array<string, string> */
    public static function colors(): array
    {
        return ['red' => 'Red', 'blue' => 'Blue'];
    }
}
