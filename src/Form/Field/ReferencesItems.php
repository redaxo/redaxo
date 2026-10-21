<?php

namespace Redaxo\Core\Form\Field;

use Redaxo\Core\Form\Reference;

/** A field whose values refer to other items, like media or articles. */
interface ReferencesItems
{
    /**
     * Returns the items the given property value refers to.
     *
     * @return iterable<Reference>
     */
    public function references(mixed $value): iterable;
}
