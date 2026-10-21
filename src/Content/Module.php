<?php

namespace Redaxo\Core\Content;

use Redaxo\Core\ClassDiscovery;
use Redaxo\Core\Content\Exception\ArticleNotFoundException;
use Redaxo\Core\Exception\LogicException;

use function sprintf;

/**
 * Base class of the modules, annotated with {@see AsModule}.
 *
 * An instance represents the module of one slice, see {@see self::of()}. What concerns the module as a whole
 * (key, name, where it is allowed) is static.
 */
abstract class Module
{
    /** @var array<string, class-string<self>>|null keyed by module key */
    private static ?array $classes = null;

    /** @var array<class-string<self>, AsModule> */
    private static array $attributes = [];

    final public function __construct(
        public readonly ArticleSlice $slice,
    ) {}

    /**
     * Returns the module of the given slice.
     *
     * Called on a concrete module class, the slice must belong to that module.
     */
    final public static function of(ArticleSlice $slice): static
    {
        $class = self::getClass($slice->moduleKey)
            ?? throw new LogicException(sprintf('The module "%s" of slice %d does not exist.', $slice->moduleKey, $slice->id));

        if (!is_a($class, static::class, true)) {
            throw new LogicException(sprintf('Slice %d belongs to module %s, not to %s.', $slice->id, $class, static::class));
        }

        /** @var static */
        return new $class($slice);
    }

    /** @return array<string, class-string<self>> keyed by module key */
    final public static function getAll(): array
    {
        if (null !== self::$classes) {
            return self::$classes;
        }

        $classes = [];
        foreach (ClassDiscovery::getInstance()->discoverByAttribute(AsModule::class, self::class) as $class => $attribute) {
            $classes[$attribute->key] = $class;
            self::$attributes[$class] = $attribute;
        }

        return self::$classes = $classes;
    }

    /** @return class-string<self>|null */
    final public static function getClass(string $key): ?string
    {
        return self::getAll()[$key] ?? null;
    }

    final public static function exists(string $key): bool
    {
        return null !== self::getClass($key);
    }

    /** Unique key, used as DB reference in rex_article_slice.module. */
    final public static function key(): string
    {
        return self::attribute()->key;
    }

    final public static function name(): string
    {
        return self::attribute()->name;
    }

    /**
     * Whether this module is allowed in the given category.
     * `null` means root level (articles without a category).
     * The default implementation allows the module everywhere.
     *
     * Override this to restrict to specific categories.
     */
    public static function isAllowedInCategory(?Category $category): bool
    {
        return true;
    }

    /** Render the backend edit form. */
    abstract public function input(): string;

    /**
     * Render the frontend output.
     *
     * @throws ArticleNotFoundException if rendering should be aborted to switch to the not found article
     */
    abstract public function output(): string;

    /** Called before slice data is saved to DB. */
    public function onPresave(ArticleSliceAction $action): void {}

    /** Called after slice data is saved to DB. */
    public function onPostsave(ArticleSliceAction $action): void {}

    private static function attribute(): AsModule
    {
        self::getAll();

        return self::$attributes[static::class] ?? throw new LogicException(sprintf('%s is not a registered module.', static::class));
    }
}
