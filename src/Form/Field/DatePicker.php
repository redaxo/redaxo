<?php

namespace Redaxo\Core\Form\Field;

use Attribute;
use Override;

/** Date picker, stored as a SQL `date` (`Y-m-d`). */
#[Attribute(Attribute::TARGET_PROPERTY)]
class DatePicker extends AbstractDateTimeField
{
    #[Override]
    protected function inputType(): string
    {
        return 'date';
    }

    #[Override]
    protected function inputFormat(): string
    {
        return 'Y-m-d';
    }

    #[Override]
    protected function parseFormats(): array
    {
        return ['Y-m-d'];
    }

    #[Override]
    protected function storageFormat(): string
    {
        return 'Y-m-d';
    }
}
