<?php

namespace Redaxo\Core\MetaInfo;

use Redaxo\Core\Exception\InvalidArgumentException;

use function array_keys;
use function implode;
use function sprintf;

/**
 * Meta values to save together with an item, converted into the column values of the tables of the entity.
 *
 * @internal
 */
final readonly class MetaValues
{
    /** @var array<string, array<non-empty-string, string|int|null>> keyed by table */
    private array $columns;

    /**
     * @param array<string, mixed> $values keyed by the storage key of the field (the column without the `meta_`
     *     prefix), each value of the type of the property
     *
     * @throws InvalidArgumentException if a key does not belong to a meta field of the entity
     */
    public function __construct(MetaEntity $entity, array $values)
    {
        $fields = [];
        foreach (MetaSchema::getFields($entity) as $field) {
            $fields[$field->definition->key] = $field;
        }

        $columns = [];
        /** @var mixed $value */
        foreach ($values as $key => $value) {
            $field = $fields[$key] ?? throw new InvalidArgumentException(sprintf(
                'Unknown meta field "%s" of %s, expected one of: %s.',
                $key,
                $entity->name,
                implode(', ', array_keys($fields)),
            ));

            // assigning to a schema instance checks the value against the property type
            $schema = new ($field->schema)();
            $field->definition->setValue($schema, $value);

            $columns[$field->table()][$field->column] = $field->toColumn($field->definition->getValue($schema));
        }
        $this->columns = $columns;
    }

    /**
     * @param non-empty-string $table the main or the translation table of the entity
     * @return array<non-empty-string, string|int|null> the column values to save into the given table
     */
    public function forTable(string $table): array
    {
        return $this->columns[$table] ?? [];
    }
}
