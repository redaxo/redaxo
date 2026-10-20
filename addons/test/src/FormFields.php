<?php

namespace Redaxo\Test;

use DateTimeImmutable;
use Redaxo\Core\Content\Article;
use Redaxo\Core\Form\Field\ArticlePicker;
use Redaxo\Core\Form\Field\Checkbox;
use Redaxo\Core\Form\Field\Choice;
use Redaxo\Core\Form\Field\DatePicker;
use Redaxo\Core\Form\Field\DateTimePicker;
use Redaxo\Core\Form\Field\MediaPicker;
use Redaxo\Core\Form\Field\Number;
use Redaxo\Core\Form\Field\Text;
use Redaxo\Core\Form\Field\Textarea;
use Redaxo\Core\Form\Field\TimePicker;
use Redaxo\Core\MediaPool\Media;

/** All form field types, shown on the form page of the test addon. */
final class FormFields
{
    private const array CHOICES = ['red' => 'Red', 'green' => 'Green', 'Dark' => ['black' => 'Black', 'navy' => 'Navy']];

    #[Text('Text', note: 'With a note', required: true, maxLength: 50)]
    public string $text = '';

    #[Textarea('Textarea', rows: 3, maxLength: 200)]
    public string $textarea = '';

    #[Number('Number', min: 1, max: 99)]
    public int $number = 1;

    #[Number('Decimal', step: 0.01)]
    public ?float $decimal = null;

    #[Checkbox('Checkbox')]
    public bool $checkbox = false;

    #[Choice('Select', choices: self::CHOICES)]
    public ?string $select = null;

    /** @var list<string> */
    #[Choice('Multi-select', choices: self::CHOICES, multiple: true)]
    public array $multiSelect = [];

    #[Choice('Radio buttons', choices: self::CHOICES, expanded: true)]
    public ?string $radios = null;

    /** @var list<string> */
    #[Choice('Checkboxes', choices: self::CHOICES, multiple: true, expanded: true)]
    public array $checkboxes = [];

    #[DatePicker('Date', min: 'today')]
    public ?DateTimeImmutable $date = null;

    #[DateTimePicker('Date and time')]
    public ?DateTimeImmutable $dateTime = null;

    #[TimePicker('Time', min: '08:00', max: '18:00')]
    public ?string $time = null;

    #[ArticlePicker('Article')]
    public ?Article $article = null;

    /** @var list<Article> */
    #[ArticlePicker('Articles', multiple: true)]
    public array $articles = [];

    #[MediaPicker('Medium', preview: true)]
    public ?Media $medium = null;

    /** @var list<Media> */
    #[MediaPicker('Media', multiple: true, types: ['jpg', 'png'])]
    public array $media = [];
}
