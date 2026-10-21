<?php

namespace Redaxo\Core\Form\Field;

use Attribute;
use Override;
use Redaxo\Core\Form\BoundField;
use Redaxo\Core\Form\InvalidValueException;
use Redaxo\Core\Form\Reference;
use Redaxo\Core\Form\ValueType;
use Redaxo\Core\Form\Widget\MediaInput;
use Redaxo\Core\Form\Widget\MediaListInput;
use Redaxo\Core\MediaPool\Media;
use Redaxo\Core\Translation\Translatable;
use Redaxo\Core\View\Renderable;

use function array_keys;
use function array_map;
use function array_values;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use function pathinfo;
use function strtolower;

use const PATHINFO_EXTENSION;

/**
 * Media pool picker for a `?Media` property, or (with `multiple`) for an ordered `array` of media.
 *
 * Storages persist the file name, or the list of file names.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class MediaPicker extends Field implements ReferencesItems
{
    /** @param list<string> $types allowed file extensions, e.g. `['jpg', 'png']`; empty allows all */
    public function __construct(
        string|Renderable $label,
        public readonly bool $multiple = false,
        string|Renderable|null $note = null,
        bool $required = false,
        ?string $key = null,
        /** Media category the media pool opens in. */
        public readonly ?int $categoryId = null,
        public readonly array $types = [],
        public readonly bool $preview = false,
    ) {
        parent::__construct($label, $note, $required, $key);
    }

    #[Override]
    public function supports(ValueType $type): bool
    {
        return $type->isAnyOf($this->multiple ? 'array' : Media::class);
    }

    #[Override]
    public function widget(BoundField $field): MediaInput|MediaListInput
    {
        $fileNames = self::splitList($field->raw);

        return $this->multiple
            ? new MediaListInput($field->inputName, $field->id, $fileNames, $this->categoryId, $this->types, $this->preview)
            : new MediaInput($field->inputName, $field->id, $fileNames[0] ?? null, $this->categoryId, $this->types, $this->preview);
    }

    /** @return string|list<string> */
    #[Override]
    public function toRaw(mixed $value): string|array
    {
        if (!$this->multiple) {
            return $value instanceof Media ? $value->fileName : '';
        }

        if (!is_array($value)) {
            return [];
        }

        $raw = [];
        foreach (array_keys($value) as $key) {
            if ($value[$key] instanceof Media) {
                $raw[] = $value[$key]->fileName;
            }
        }

        return $raw;
    }

    /** @return Media|list<Media>|null */
    #[Override]
    public function fromRaw(string|array|null $raw, ValueType $type): Media|array|null
    {
        if (!$this->multiple && !is_string($raw ?? '')) {
            throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        $media = array_map($this->media(...), self::splitList($raw));

        return $this->multiple ? $media : ($media[0] ?? null);
    }

    /** @return string|list<string>|null */
    #[Override]
    public function encode(mixed $value): string|array|null
    {
        $raw = $this->toRaw($value);

        return $this->multiple || '' !== $raw ? $raw : null;
    }

    /** @return Media|list<Media>|null */
    #[Override]
    public function decode(mixed $stored, ValueType $type): Media|array|null
    {
        if ($this->multiple) {
            return is_array($stored) ? array_map($this->media(...), array_values($stored)) : throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        return null === $stored || '' === $stored ? null : $this->media($stored);
    }

    /** @return list<Reference> */
    #[Override]
    public function references(mixed $value): array
    {
        $references = [];
        foreach ((array) $this->toRaw($value) as $fileName) {
            if ('' === $fileName) {
                continue;
            }
            $references[] = Reference::media($fileName);
        }

        return $references;
    }

    private function media(mixed $fileName): Media
    {
        $media = is_string($fileName) ? Media::get($fileName) : null;

        if (null === $media) {
            throw new InvalidValueException(new Translatable('form_media_not_found'));
        }

        if ($this->types && !in_array(strtolower(pathinfo($media->fileName, PATHINFO_EXTENSION)), $this->types, true)) {
            throw new InvalidValueException(new Translatable('form_media_invalid_type', implode(', ', $this->types)));
        }

        return $media;
    }
}
