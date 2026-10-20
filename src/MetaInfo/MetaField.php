<?php

namespace Redaxo\Core\MetaInfo;

use BackedEnum;
use BcMath\Number as Decimal;
use Redaxo\Core\Database\Column;
use Redaxo\Core\Exception\LogicException;
use Redaxo\Core\Form\Field\ArticlePicker;
use Redaxo\Core\Form\Field\Checkbox;
use Redaxo\Core\Form\Field\DatePicker;
use Redaxo\Core\Form\Field\DateTimePicker;
use Redaxo\Core\Form\Field\Number;
use Redaxo\Core\Form\Field\Text;
use Redaxo\Core\Form\Field\Textarea;
use Redaxo\Core\Form\Field\TimePicker;
use Redaxo\Core\Form\FieldDefinition;
use Redaxo\Core\Form\InvalidValueException;
use ReflectionEnum;
use ReflectionNamedType;

use function array_map;
use function explode;
use function implode;
use function is_array;
use function is_bool;
use function is_float;
use function is_scalar;
use function is_string;
use function min;
use function number_format;
use function sprintf;
use function strlen;
use function strrchr;
use function substr;

/**
 * A field of a {@see MetaSchema} together with the column it is stored in.
 *
 * Converts between the property values and the column values: lists are stored comma-separated (so that they stay
 * queryable via SQL `FIND_IN_SET()`), booleans as `0`/`1`.
 *
 * @internal
 */
final readonly class MetaField
{
    /** Property name. */
    public string $property;

    /** @var non-empty-string */
    public string $column;

    /** Whether the value is maintained per language, see {@see Translated}. */
    public bool $translated;

    /** @param class-string<MetaSchema> $schema */
    public function __construct(
        public string $schema,
        public MetaEntity $entity,
        public FieldDefinition $definition,
    ) {
        $this->property = $definition->name;
        $this->column = MetaSchema::COLUMN_PREFIX . $definition->key;
        $this->translated = $definition->hasAttribute(Translated::class);
    }

    /**
     * The table the field is stored in.
     *
     * @return non-empty-string
     */
    public function table(): string
    {
        return $this->translated ? $this->entity->translationTable() ?? $this->entity->table() : $this->entity->table();
    }

    /** The column definition, derived from the field and the property type unless the schema overrides it. */
    public function columnDefinition(): Column
    {
        $override = $this->schema::column($this->property, $this->column);
        if (null !== $override) {
            return $override;
        }

        $field = $this->definition->field;
        $type = $this->definition->type;
        $name = $this->column;

        return match (true) {
            $field instanceof Checkbox => Column::bool($name, default: false),
            $field instanceof Textarea, 'array' === $type->name => Column::text($name, nullable: true),
            $field instanceof DatePicker => Column::date($name, nullable: true),
            $field instanceof DateTimePicker => Column::datetime($name, nullable: true),
            $field instanceof TimePicker => Column::time($name, nullable: true),
            $field instanceof ArticlePicker => Column::int($name, unsigned: true, nullable: true),
            'int' === $type->name || $this->isIntEnum() => Column::int($name, nullable: true),
            'float' === $type->name => Column::double($name, nullable: true),
            $type->is(Decimal::class) => $this->decimalColumn(),
            $field instanceof Text => Column::varchar($name, $field->maxLength ?? 255, nullable: true),
            default => Column::varchar($name, 255, nullable: true),
        };
    }

    /** Converts the property value into the column value. */
    public function toColumn(mixed $value): string|int|null
    {
        $encoded = $this->definition->field->encode($value);

        return match (true) {
            null === $encoded => null,
            is_array($encoded) => implode(',', array_map(static fn (mixed $item): string => is_scalar($item) ? (string) $item : '', $encoded)),
            is_bool($encoded) => (int) $encoded,
            is_float($encoded) => (string) $encoded,
            default => $encoded,
        };
    }

    /**
     * Writes the column value into the given schema instance.
     *
     * Values that are missing or no longer fit the property type are skipped, so the property keeps its default.
     */
    public function hydrate(MetaSchema $schema, mixed $stored): void
    {
        if ('array' === $this->definition->type->name) {
            $stored = null === $stored || '' === $stored ? [] : explode(',', (string) $stored);
        }

        try {
            $value = $this->definition->field->decode($stored, $this->definition->type);
        } catch (InvalidValueException) {
            return;
        }

        $this->definition->setValue($schema, $value);
    }

    /** A decimal column with as many decimals as the step of the number field. */
    private function decimalColumn(): Column
    {
        $field = $this->definition->field;

        if (!$field instanceof Number || null === $field->step) {
            throw new LogicException(sprintf(
                'The meta field %s::$%s needs a `Number` field with a `step` to derive its decimal column, or override %s::column().',
                $this->schema,
                $this->property,
                $this->schema,
            ));
        }

        $step = is_string($field->step) ? $field->step : number_format($field->step, 10, '.', '');
        $decimals = min(30, strlen(rtrim(substr(strrchr($step, '.') ?: '.', 1), '0')));

        return Column::decimal($this->column, 20, $decimals, nullable: true);
    }

    private function isIntEnum(): bool
    {
        $type = $this->definition->type;

        if (!$type->is(BackedEnum::class)) {
            return false;
        }

        $backingType = new ReflectionEnum($type->name)->getBackingType();

        return $backingType instanceof ReflectionNamedType && 'int' === $backingType->getName();
    }
}
