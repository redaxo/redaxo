<?php

namespace Redaxo\Core\Form\Storage;

use Redaxo\Core\Config;
use Redaxo\Core\Form\FormSchema;
use Redaxo\Core\Form\InvalidValueException;

/**
 * Loads and saves the field properties of a data object from/to a {@see Config} namespace.
 *
 * Each field is stored under its key. Stored values that are missing or no longer fit the property type are
 * skipped on load, so the property keeps its default.
 */
final readonly class ConfigStorage
{
    public function __construct(
        public string $namespace,
    ) {}

    public function load(object $data): void
    {
        foreach (FormSchema::of($data::class)->fields as $definition) {
            if (!Config::has($this->namespace, $definition->key)) {
                continue;
            }

            try {
                $value = $definition->field->decode(Config::get($this->namespace, $definition->key), $definition->type);
            } catch (InvalidValueException) {
                continue;
            }

            $definition->setValue($data, $value);
        }
    }

    public function save(object $data): void
    {
        foreach (FormSchema::of($data::class)->fields as $definition) {
            if ($definition->isInitialized($data)) {
                Config::set($this->namespace, $definition->key, $definition->field->encode($definition->getValue($data)));
            }
        }
    }
}
