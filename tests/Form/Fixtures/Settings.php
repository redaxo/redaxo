<?php

namespace Redaxo\Core\Tests\Form\Fixtures;

use Redaxo\Core\Form\Field\Checkbox;
use Redaxo\Core\Form\Field\Choice;
use Redaxo\Core\Form\Field\Text;

/** @internal */
final class Settings
{
    #[Text(
        'Title',
        required: true,
        maxLength: 20,
    )]
    public string $title = 'Hello';

    #[Text('Subtitle')]
    public ?string $subtitle = null;

    #[Choice(
        'Size',
        choices: [1 => 'Small', 2 => 'Large'],
        key: 'size_id',
    )]
    public int $size = 1;

    #[Choice(
        'Color',
        choices: self::colors(...),
    )]
    public ?Color $color = null;

    #[Checkbox('Active')]
    public bool $active = true;

    public string $notAField = 'untouched';

    /** @return array<string, string> */
    public static function colors(): array
    {
        return ['red' => 'Red', 'blue' => 'Blue'];
    }
}
