<?php

namespace Redaxo\Core\MetaInfo;

use Redaxo\Core\ClassDiscovery;
use Redaxo\Core\Database\Column;
use Redaxo\Core\Exception\LogicException;
use Redaxo\Core\Form\Field;
use Redaxo\Core\Form\FormSchema;
use Redaxo\Core\Translation\Translatable;
use ReflectionClass;
use WeakMap;

use function sprintf;

/**
 * Declarative definition of meta fields of one {@see MetaEntity}, annotated with {@see AsMetaSchema}.
 *
 * The fields are the properties of the schema, annotated with form field types (see {@see Field}).
 * Each field is stored in a column named after its key with the {@see self::COLUMN_PREFIX}: the property name in
 * snake_case (`$seoTitle` → `meta_seo_title`), or the `key` of the field, e.g. to keep the column of a renamed property.
 * An entity may have several schemas, e.g. one of an addon and one of the project, but their columns must not collide.
 *
 * The typed values of an item are read with {@see self::of()}:
 *
 * ```php
 * SeoMeta::of($article)->title;
 * ```
 */
abstract class MetaSchema
{
    /** Prefix every meta column carries, so that meta columns are distinguishable from the columns owned by core. */
    public const string COLUMN_PREFIX = 'meta_';

    /** @var array<string, list<class-string<self>>>|null keyed by MetaEntity case name */
    private static ?array $schemas = null;

    /** @var array<string, list<MetaField>> keyed by MetaEntity case name */
    private static array $fields = [];

    /** @var array<class-string<self>, list<MetaField>> */
    private static array $classFields = [];

    /** @var array<class-string<self>, WeakMap<object, self>> */
    private static array $instances = [];

    final public function __construct() {}

    /**
     * Returns the meta values of the given item (an article, category, medium or language, matching the entity of
     * the schema).
     *
     * The instance is shared per item, changes to it are not persisted.
     */
    final public static function of(object $subject): static
    {
        $entity = self::entityOf(static::class);
        if (!$entity->accepts($subject)) {
            throw new LogicException(sprintf('Meta schema %s can not read the meta values of a %s.', static::class, $subject::class));
        }

        if (!isset(self::$instances[static::class])) {
            /** @var WeakMap<object, self> $map */
            $map = new WeakMap();
            self::$instances[static::class] = $map;
        }

        $instances = self::$instances[static::class];
        if (!isset($instances[$subject])) {
            $instances[$subject] = self::load($subject);
        }

        /** @var static */
        return $instances[$subject];
    }

    /**
     * Whether the given field is shown and saved in the given context, e.g. only for articles of a certain category.
     *
     * The default allows all fields everywhere.
     */
    public static function isFieldAllowed(string $property, MetaContext $context): bool
    {
        return true;
    }

    /**
     * The column for the given field, overriding the one derived from the field type and the property type.
     *
     * Return `null` to keep the derived column.
     *
     * @param non-empty-string $name the column name
     */
    public static function column(string $property, string $name): ?Column
    {
        return null;
    }

    /**
     * Cross-field validation, called after all fields are valid and written into the instance.
     *
     * Return an error message to reject, or `null` if valid.
     */
    public function validate(): string|Translatable|null
    {
        return null;
    }

    /**
     * Creates a new instance holding the stored values of the given item, or the defaults for `null`.
     *
     * @internal
     */
    final public static function load(?object $subject): static
    {
        $class = static::class;
        $schema = new $class();

        if (null === $subject) {
            return $schema;
        }

        foreach (self::fieldsOf(static::class) as $field) {
            // The items share no common interface yet, see MetaContext. Columns missing in the item (e.g. a database row
            // of another table) keep the default.
            /** @psalm-suppress MixedMethodCall */
            if ($subject->hasValue($field->column)) {
                /** @psalm-suppress MixedMethodCall */
                $field->hydrate($schema, $subject->getValue($field->column));
            }
        }

        return $schema;
    }

    /**
     * Returns the schema classes of the given entity.
     *
     * @return list<class-string<self>>
     *
     * @internal
     */
    final public static function getSchemas(MetaEntity $entity): array
    {
        if (null === self::$schemas) {
            $schemas = [];
            foreach (ClassDiscovery::getInstance()->discoverByAttribute(AsMetaSchema::class, self::class) as $class => $attribute) {
                $schemas[$attribute->entity->name][] = $class;
            }
            self::$schemas = $schemas;
        }

        return self::$schemas[$entity->name] ?? [];
    }

    /**
     * Returns the fields of all schemas of the given entity.
     *
     * @return list<MetaField>
     *
     * @internal
     */
    final public static function getFields(MetaEntity $entity): array
    {
        if (isset(self::$fields[$entity->name])) {
            return self::$fields[$entity->name];
        }

        $fields = [];
        $columns = [];
        foreach (self::getSchemas($entity) as $class) {
            foreach (self::fieldsOf($class) as $field) {
                if (isset($columns[$field->column])) {
                    throw new LogicException(sprintf(
                        'The meta fields %s::$%s and %s::$%s both use the column "%s".',
                        $columns[$field->column]->schema,
                        $columns[$field->column]->property,
                        $field->schema,
                        $field->property,
                        $field->column,
                    ));
                }
                $columns[$field->column] = $field;
                $fields[] = $field;
            }
        }

        return self::$fields[$entity->name] = $fields;
    }

    /**
     * @param class-string<self> $class
     * @return list<MetaField>
     *
     * @internal
     */
    final public static function fieldsOf(string $class): array
    {
        if (isset(self::$classFields[$class])) {
            return self::$classFields[$class];
        }

        $entity = self::entityOf($class);

        $fields = [];
        foreach (FormSchema::of($class)->fields as $definition) {
            $fields[] = new MetaField($class, $entity, $definition);
        }

        return self::$classFields[$class] = $fields;
    }

    /** @param class-string<self> $class */
    private static function entityOf(string $class): MetaEntity
    {
        $attribute = new ReflectionClass($class)->getAttributes(AsMetaSchema::class)[0] ?? null;

        return $attribute?->newInstance()->entity ?? throw new LogicException(sprintf('Meta schema %s is not annotated with #[AsMetaSchema].', $class));
    }
}
