<?php

namespace Redaxo\Core\Form\Storage;

use JsonException;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Form\FormSchema;
use Redaxo\Core\Form\InvalidValueException;

use function is_array;
use function is_bool;
use function is_string;
use function json_decode;
use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * Loads and saves the field properties of a data object from/to a table row, identified by its id; new rows get
 * their id by auto increment.
 *
 * Each field is stored in the column named by its key, array values as JSON. Columns that are missing or hold a value
 * no longer fitting the property type are skipped on load, so the property keeps its default.
 */
final class DatabaseStorage
{
    public function __construct(
        public readonly string $table,
        /** Id of the row; `null` for a new row, set by {@see self::save()} after inserting it. */
        public private(set) int|string|null $id = null,
        public readonly string $idColumn = 'id',
        /** Whether to set the `createdate`/`createuser` and `updatedate`/`updateuser` columns. */
        public readonly bool $globalFields = false,
    ) {}

    /** @return bool whether the row exists */
    public function load(object $data): bool
    {
        if (null === $this->id) {
            return false;
        }

        $sql = Sql::factory();
        $sql->setTable($this->table);
        $sql->setWhere([$this->idColumn => $this->id]);
        $sql->select();

        if (!$sql->getRows()) {
            return false;
        }

        foreach (FormSchema::of($data::class)->fields as $definition) {
            if (!$sql->hasValue($definition->key)) {
                continue;
            }

            $stored = $sql->getValue($definition->key);

            try {
                $value = $definition->field->decode('array' === $definition->type->name ? self::decodeJson($stored) : $stored, $definition->type);
            } catch (InvalidValueException|JsonException) {
                continue;
            }

            $definition->setValue($data, $value);
        }

        return true;
    }

    /** Updates the row, or inserts it if there is no id yet. */
    public function save(object $data): void
    {
        $sql = Sql::factory();
        $sql->setTable($this->table);

        foreach (FormSchema::of($data::class)->fields as $definition) {
            if (!$definition->isInitialized($data)) {
                continue;
            }

            $value = $definition->field->encode($definition->getValue($data));
            $sql->setValue($definition->key, match (true) {
                is_array($value) => json_encode($value, JSON_THROW_ON_ERROR),
                is_bool($value) => (int) $value,
                default => $value,
            });
        }

        if ($this->globalFields) {
            $sql->addGlobalUpdateFields();
        }

        if (null !== $this->id) {
            $sql->setWhere([$this->idColumn => $this->id]);
            $sql->update();

            return;
        }

        if ($this->globalFields) {
            $sql->addGlobalCreateFields();
        }
        $sql->insert();
        $this->id = $sql->getLastId();
    }

    /** @throws JsonException */
    private static function decodeJson(string|int|float|bool|null $stored): mixed
    {
        return is_string($stored) && '' !== $stored ? json_decode($stored, true, flags: JSON_THROW_ON_ERROR) : null;
    }
}
