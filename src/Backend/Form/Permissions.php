<?php

namespace Redaxo\Core\Backend\Form;

use Attribute;
use Override;
use Redaxo\Core\Backend\Controller;
use Redaxo\Core\Backend\Page;
use Redaxo\Core\Form\BoundField;
use Redaxo\Core\Form\ChoiceProvider;
use Redaxo\Core\Form\Field\Field;
use Redaxo\Core\Form\FieldGroup;
use Redaxo\Core\Form\InvalidValueException;
use Redaxo\Core\Form\ValueType;
use Redaxo\Core\Security\ComplexPermission;
use Redaxo\Core\Security\Permission;
use Redaxo\Core\Security\PermissionChoices;
use Redaxo\Core\Translation\Translatable;
use Redaxo\Core\View\Renderable;

use function array_fill_keys;
use function array_keys;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use function str_contains;
use function strpos;
use function substr;
use function trim;

/**
 * All permissions of a role: the simple permission groups (general, options, extras) and the registered
 * {@see ComplexPermission}s, each with a checkbox granting it for all items.
 *
 * The property holds the selected items per permission key, or {@see ComplexPermission::ALL}. The raw value is a single
 * list of `key:item` entries plus a bare `key` for "all", so all permissions can be submitted under one field name.
 *
 * @internal
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Permissions extends Field
{
    private const array GROUPS = [Permission::GENERAL, Permission::OPTIONS, Permission::EXTRAS];

    #[Override]
    public function supports(ValueType $type): bool
    {
        return $type->isAnyOf('array');
    }

    #[Override]
    public function widget(BoundField $field): PermissionsInput
    {
        $selected = [];
        $all = [];
        foreach (self::splitList($field->raw) as $item) {
            [$key, $value] = self::split($item);
            if (null === $value) {
                $all[$key] = true;
            } else {
                $selected[$key][] = $item;
            }
        }

        $groups = [];
        foreach (self::GROUPS as $group) {
            $groups[] = [
                'key' => $group,
                'label' => new Translatable('user_' . $group),
                'allLabel' => null,
                'choices' => self::prefix($group, self::groupChoices($group)),
                'selected' => $selected[$group] ?? [],
                'all' => false,
            ];
        }

        foreach (self::complexChoices() as $key => $choices) {
            $groups[] = [
                'key' => $key,
                'label' => $choices->label,
                'allLabel' => $choices->allLabel,
                'choices' => self::prefix($key, self::resolve($choices->choices)),
                'selected' => $selected[$key] ?? [],
                'all' => isset($all[$key]),
            ];
        }

        return new PermissionsInput($field->inputName, $field->id, $groups);
    }

    #[Override]
    public function row(BoundField $field): Renderable
    {
        $widget = $this->widget($field);
        $widget->error = $field->error;

        return new FieldGroup($widget->render(), $this->label);
    }

    /** @return list<string> */
    #[Override]
    public function toRaw(mixed $value): array
    {
        $raw = [];
        foreach (self::normalize($value) as $key => $items) {
            if (ComplexPermission::ALL === $items) {
                $raw[] = $key;
                continue;
            }
            foreach ($items as $item) {
                $raw[] = $key . ':' . $item;
            }
        }

        return $raw;
    }

    /** @return array<string, list<string>|ComplexPermission::ALL> */
    #[Override]
    public function fromRaw(string|array|null $raw, ValueType $type): array
    {
        $choices = [];
        foreach (self::GROUPS as $group) {
            $choices[$group] = self::flatten(self::groupChoices($group));
        }
        foreach (self::complexChoices() as $key => $complex) {
            $choices[$key] = self::flatten(self::resolve($complex->choices));
        }

        $value = array_fill_keys(array_keys($choices), []);
        $all = [];
        foreach (self::splitList($raw) as $entry) {
            [$key, $item] = self::split($entry);
            if (null === $item) {
                if (!isset($choices[$key]) || in_array($key, self::GROUPS, true)) {
                    throw new InvalidValueException(new Translatable('form_invalid_choice'));
                }
                $all[$key] = true;
                continue;
            }

            if (!isset($choices[$key][$item])) {
                throw new InvalidValueException(new Translatable('form_invalid_choice'));
            }
            $value[$key][] = $item;
        }

        foreach ($all as $key => $_) {
            $value[$key] = ComplexPermission::ALL;
        }

        return $value;
    }

    /**
     * Stores each permission like the old role form did: the items as `|a|b|`, or {@see ComplexPermission::ALL}.
     *
     * @return array<string, string>
     */
    #[Override]
    public function encode(mixed $value): array
    {
        $stored = [];
        foreach (self::normalize($value) as $key => $items) {
            $stored[$key] = match (true) {
                ComplexPermission::ALL === $items => ComplexPermission::ALL,
                [] === $items => '',
                default => '|' . implode('|', $items) . '|',
            };
        }

        return $stored;
    }

    /** @return array<string, list<string>|ComplexPermission::ALL> */
    #[Override]
    public function decode(mixed $stored, ValueType $type): array
    {
        if (!is_array($stored)) {
            throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        $value = [];
        foreach ($stored as $key => $items) {
            if (!is_string($items)) {
                continue;
            }

            if (ComplexPermission::ALL === $items && !in_array($key, self::GROUPS, true)) {
                $value[(string) $key] = ComplexPermission::ALL;
                continue;
            }

            $value[(string) $key] = '' === trim($items, '|') ? [] : explode('|', trim($items, '|'));
        }

        return $value;
    }

    /**
     * Splits a raw item into the permission key and the item, which is `null` for "all".
     *
     * @return array{string, string|null}
     */
    private static function split(string $raw): array
    {
        $pos = strpos($raw, ':');

        return false === $pos ? [$raw, null] : [substr($raw, 0, $pos), substr($raw, $pos + 1)];
    }

    /** @return array<string, list<string>|ComplexPermission::ALL> */
    private static function normalize(mixed $value): array
    {
        if (!is_array($value)) {
            throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        $normalized = [];
        foreach (array_keys($value) as $key) {
            $items = $value[$key];
            if (ComplexPermission::ALL === $items) {
                $normalized[(string) $key] = ComplexPermission::ALL;
                continue;
            }
            if (!is_array($items)) {
                throw new InvalidValueException(new Translatable('form_invalid_value'));
            }

            $list = [];
            foreach (array_keys($items) as $index) {
                $list[] = self::scalarToRaw($items[$index]);
            }
            $normalized[(string) $key] = $list;
        }

        return $normalized;
    }

    /** @return array<string, string> */
    private static function groupChoices(string $group): array
    {
        self::registerPagePermissions(Controller::getPages());

        /** @var Permission::* $group */
        return Permission::getAll($group);
    }

    /**
     * Registers the permissions the backend pages require, if no addon registered them explicitly.
     *
     * @param array<Page> $pages
     */
    private static function registerPagePermissions(array $pages): void
    {
        foreach ($pages as $page) {
            foreach ($page->getRequiredPermissions() as $perm) {
                // complex permissions contain a slash
                if ($perm && !in_array($perm, ['isAdmin', 'admin', 'admin[]'], true) && !str_contains($perm, '/') && !Permission::has($perm)) {
                    Permission::register($perm);
                }
            }
            self::registerPagePermissions($page->getSubpages());
        }
    }

    /** @return array<string, PermissionChoices> */
    private static function complexChoices(): array
    {
        $choices = [];
        foreach (ComplexPermission::getAll() as $key => $class) {
            if ($complex = $class::getChoices()) {
                $choices[$key] = $complex;
            }
        }

        return $choices;
    }

    /**
     * @param array<int|string, string|Translatable|array<int|string, string|Translatable>>|ChoiceProvider $choices
     * @return array<int|string, string|Translatable|array<int|string, string|Translatable>>
     */
    private static function resolve(array|ChoiceProvider $choices): array
    {
        return $choices instanceof ChoiceProvider ? $choices->choices() : $choices;
    }

    /**
     * @param array<int|string, string|Translatable|array<int|string, string|Translatable>> $choices
     * @return array<int|string, string|Translatable>
     */
    private static function flatten(array $choices): array
    {
        $flat = [];
        foreach ($choices as $value => $label) {
            if (is_array($label)) {
                $flat += $label;
            } else {
                $flat[$value] = $label;
            }
        }

        return $flat;
    }

    /**
     * @param array<int|string, string|Translatable|array<int|string, string|Translatable>> $choices
     * @return array<string, string|Translatable|array<string, string|Translatable>>
     */
    private static function prefix(string $key, array $choices): array
    {
        $prefixed = [];
        foreach ($choices as $value => $label) {
            $prefixed[is_array($label) ? (string) $value : $key . ':' . $value] = is_array($label) ? self::prefix($key, $label) : $label;
        }

        /** @var array<string, string|Translatable|array<string, string|Translatable>> */
        return $prefixed;
    }
}
