<?php

namespace Redaxo\Core\Form\Field;

use Attribute;
use Override;
use Redaxo\Core\Content\Article;
use Redaxo\Core\Form\BoundField;
use Redaxo\Core\Form\InvalidValueException;
use Redaxo\Core\Form\ValueType;
use Redaxo\Core\Form\Widget\ArticleInput;
use Redaxo\Core\Form\Widget\ArticleListInput;
use Redaxo\Core\Translation\Translatable;
use Redaxo\Core\View\Renderable;

use function array_keys;
use function array_map;
use function array_values;
use function filter_var;
use function is_array;
use function is_string;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_INT;

/**
 * Linkmap picker for an `?Article` property, or (with `multiple`) for an ordered `array` of articles.
 *
 * Storages persist the article id, or the list of ids.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ArticlePicker extends Field
{
    public function __construct(
        string|Renderable $label,
        public readonly bool $multiple = false,
        string|Renderable|null $note = null,
        bool $required = false,
        ?string $key = null,
        /** Category the linkmap opens in when no article is selected. */
        public readonly ?int $categoryId = null,
    ) {
        parent::__construct($label, $note, $required, $key);
    }

    #[Override]
    public function supports(ValueType $type): bool
    {
        return $type->isAnyOf($this->multiple ? 'array' : Article::class);
    }

    #[Override]
    public function widget(BoundField $field): ArticleInput|ArticleListInput
    {
        $ids = array_map(intval(...), self::splitList($field->raw));

        return $this->multiple
            ? new ArticleListInput($field->inputName, $field->id, $ids, $this->categoryId, disabled: $field->disabled)
            : new ArticleInput($field->inputName, $field->id, $ids[0] ?? null, $this->categoryId, disabled: $field->disabled);
    }

    /** @return string|list<string> */
    #[Override]
    public function toRaw(mixed $value): string|array
    {
        if (!$this->multiple) {
            return $value instanceof Article ? (string) $value->id : '';
        }

        if (!is_array($value)) {
            return [];
        }

        $raw = [];
        foreach (array_keys($value) as $key) {
            if ($value[$key] instanceof Article) {
                $raw[] = (string) $value[$key]->id;
            }
        }

        return $raw;
    }

    /** @return Article|list<Article>|null */
    #[Override]
    public function fromRaw(string|array|null $raw, ValueType $type): Article|array|null
    {
        if (!$this->multiple && !is_string($raw ?? '')) {
            throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        $articles = array_map(self::article(...), self::splitList($raw));

        return $this->multiple ? $articles : ($articles[0] ?? null);
    }

    /** @return int|list<int>|null */
    #[Override]
    public function encode(mixed $value): int|array|null
    {
        if (!$this->multiple) {
            return $value instanceof Article ? $value->id : null;
        }

        return array_map(intval(...), (array) $this->toRaw($value));
    }

    /** @return Article|list<Article>|null */
    #[Override]
    public function decode(mixed $stored, ValueType $type): Article|array|null
    {
        if ($this->multiple) {
            return is_array($stored) ? array_map(self::article(...), array_values($stored)) : throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        return null === $stored || '' === $stored ? null : self::article($stored);
    }

    private static function article(mixed $id): Article
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        return (null === $id ? null : Article::get($id)) ?? throw new InvalidValueException(new Translatable('form_article_not_found'));
    }
}
