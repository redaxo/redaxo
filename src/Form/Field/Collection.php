<?php

namespace Redaxo\Core\Form\Field;

use Attribute;
use Override;
use Redaxo\Core\Form\BoundField;
use Redaxo\Core\Form\Form;
use Redaxo\Core\Form\FormSchema;
use Redaxo\Core\Form\InvalidValueException;
use Redaxo\Core\Form\Reference;
use Redaxo\Core\Form\ValueType;
use Redaxo\Core\Form\Widget\CollectionInput;
use Redaxo\Core\Translation\Translatable;
use Redaxo\Core\View\Renderable;
use ReflectionClass;

use function array_keys;
use function count;
use function is_array;
use function is_object;
use function preg_replace;

/**
 * An ordered list of items for an `array` property, each item an instance of the given class with its own fields.
 *
 * ```php
 * /** @var list<Slide> *\/
 * #[Collection(
 *     'Slides',
 *     item: Slide::class,
 * )]
 * public array $slides = [];
 * ```
 *
 * The item class needs a constructor without parameters. Storages persist a list of the stored values of the items,
 * keyed by storage key like the fields of the form itself, so it needs a storage supporting nested values (JSON).
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Collection extends Field implements ReferencesItems
{
    /** @param class-string $item */
    public function __construct(
        string|Renderable $label,
        public readonly string $item,
        string|Renderable|null $note = null,
        /** Whether at least one item is required. */
        bool $required = false,
        ?string $key = null,
        /** @var positive-int|null minimum number of items, unless the collection is empty (see `$required`) */
        public readonly ?int $min = null,
        /** @var positive-int|null */
        public readonly ?int $max = null,
    ) {
        parent::__construct($label, $note, $required, $key);
    }

    #[Override]
    public function supports(ValueType $type): bool
    {
        return $type->isAnyOf('array');
    }

    #[Override]
    public function widget(BoundField $field): CollectionInput
    {
        // word characters only, so that the placeholder is kept by every escaping, e.g. in the onclick of a picker
        $placeholder = '__' . (string) preg_replace('/\W/', '_', $field->id) . '__';

        $items = [];
        foreach ($this->rawItems($field->raw) as $index => $raw) {
            $form = $this->itemForm($field->inputName . '[' . $index . ']');
            // binds the raw values for display, showing the errors of invalid ones
            $form->submit($raw);
            $items[] = $form->rest();
        }

        $template = $this->itemForm($field->inputName . '[' . $placeholder . ']')->rest();

        return new CollectionInput($field->id, $items, $template, $placeholder, $this->max);
    }

    /** @return list<array<string, string|array<array-key, mixed>>> */
    #[Override]
    public function toRaw(mixed $value): array
    {
        $schema = FormSchema::of($this->item);

        $raw = [];
        foreach ($this->items($value) as $item) {
            $itemRaw = [];
            foreach ($schema->fields as $name => $definition) {
                $itemRaw[$name] = $definition->field->toRaw($definition->getValue($item));
            }
            $raw[] = $itemRaw;
        }

        return $raw;
    }

    /** @return list<object> */
    #[Override]
    public function fromRaw(string|array|null $raw, ValueType $type): array
    {
        $items = [];
        $valid = true;
        foreach ($this->rawItems($raw) as $itemRaw) {
            $item = $this->newItem();
            if (!$this->itemForm('item', $item)->submit($itemRaw)) {
                $valid = false;
            }
            $items[] = $item;
        }

        if (!$valid) {
            throw new InvalidValueException(new Translatable('form_collection_invalid'));
        }

        $this->checkCount($items);

        return $items;
    }

    /** @return list<array<string, string|int|float|bool|array<array-key, mixed>|null>> */
    #[Override]
    public function encode(mixed $value): array
    {
        $schema = FormSchema::of($this->item);

        $stored = [];
        foreach ($this->items($value) as $item) {
            $stored[] = $schema->encode($item);
        }

        return $stored;
    }

    /** @return list<object> */
    #[Override]
    public function decode(mixed $stored, ValueType $type): array
    {
        if (!is_array($stored)) {
            throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        $schema = FormSchema::of($this->item);

        $items = [];
        foreach (array_keys($stored) as $key) {
            if (is_array($stored[$key])) {
                $item = $this->newItem();
                $schema->hydrate($item, $stored[$key]);
                $items[] = $item;
            }
        }

        return $items;
    }

    /** @return list<Reference> */
    #[Override]
    public function references(mixed $value): array
    {
        $schema = FormSchema::of($this->item);

        $references = [];
        foreach ($this->items($value) as $item) {
            foreach ($schema->references($item) as $reference) {
                $references[] = $reference;
            }
        }

        return $references;
    }

    private function newItem(): object
    {
        return new ReflectionClass($this->item)->newInstance();
    }

    private function itemForm(string $name, ?object $item = null): Form
    {
        return new Form($item ?? $this->newItem(), name: $name, csrf: false, vertical: true);
    }

    /** @return list<object> */
    private function items(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $items = [];
        foreach (array_keys($value) as $key) {
            if (is_object($value[$key]) && $value[$key] instanceof $this->item) {
                $items[] = $value[$key];
            }
        }

        return $items;
    }

    /**
     * The raw values of the items, in the submitted order; the keys of the request are only used to group the values.
     *
     * @param string|array<array-key, mixed>|null $raw
     * @return list<array<array-key, mixed>>
     */
    private function rawItems(string|array|null $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $items = [];
        foreach (array_keys($raw) as $key) {
            if (is_array($raw[$key])) {
                $items[] = $raw[$key];
            }
        }

        return $items;
    }

    /** @param list<object> $items */
    private function checkCount(array $items): void
    {
        if (null !== $this->min && $items && count($items) < $this->min) {
            throw new InvalidValueException(new Translatable('form_collection_min', $this->min));
        }

        if (null !== $this->max && count($items) > $this->max) {
            throw new InvalidValueException(new Translatable('form_collection_max', $this->max));
        }
    }
}
