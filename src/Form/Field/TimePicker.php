<?php

namespace Redaxo\Core\Form\Field;

use Attribute;
use DateTimeImmutable;
use Override;
use Redaxo\Core\Util\Formatter;

/** Time picker, stored as a SQL `time` (`H:i:s`); a `DateTimeImmutable` property carries the date 1970-01-01. */
#[Attribute(Attribute::TARGET_PROPERTY)]
class TimePicker extends AbstractDateTimeField
{
    #[Override]
    protected function inputType(): string
    {
        return 'time';
    }

    #[Override]
    protected function inputFormat(): string
    {
        return 'H:i';
    }

    #[Override]
    protected function parseFormats(): array
    {
        return ['H:i', 'H:i:s'];
    }

    #[Override]
    protected function storageFormat(): string
    {
        return 'H:i:s';
    }

    #[Override]
    protected function display(DateTimeImmutable $date): string
    {
        return Formatter::intlTime($date);
    }
}
