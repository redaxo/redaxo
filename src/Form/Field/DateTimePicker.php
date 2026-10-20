<?php

namespace Redaxo\Core\Form\Field;

use Attribute;
use DateTimeImmutable;
use Override;
use Redaxo\Core\Util\Formatter;

/** Date and time picker, stored as a SQL `datetime` (`Y-m-d H:i:s`). */
#[Attribute(Attribute::TARGET_PROPERTY)]
class DateTimePicker extends AbstractDateTimeField
{
    #[Override]
    protected function inputType(): string
    {
        return 'datetime-local';
    }

    #[Override]
    protected function inputFormat(): string
    {
        return 'Y-m-d\TH:i';
    }

    #[Override]
    protected function parseFormats(): array
    {
        return ['Y-m-d\TH:i', 'Y-m-d\TH:i:s'];
    }

    #[Override]
    protected function storageFormat(): string
    {
        return 'Y-m-d H:i:s';
    }

    #[Override]
    protected function display(DateTimeImmutable $date): string
    {
        return Formatter::intlDateTime($date);
    }
}
