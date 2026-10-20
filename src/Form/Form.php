<?php

namespace Redaxo\Core\Form;

use Closure;
use Redaxo\Core\Backend\Accesskey;
use Redaxo\Core\Exception\LogicException;
use Redaxo\Core\Http\Request;
use Redaxo\Core\Security\CsrfToken;
use Redaxo\Core\Translation\Translatable;
use Redaxo\Core\View\Html;
use Redaxo\Core\View\HtmlAttributes;
use Redaxo\Core\View\Renderable;
use Redaxo\Core\View\RendersView;
use ReflectionClass;

use function array_flip;
use function array_intersect_key;
use function array_key_exists;
use function array_key_last;
use function array_keys;
use function implode;
use function is_array;
use function is_string;
use function lcfirst;
use function preg_replace;
use function Redaxo\Core\View\escape;
use function sprintf;

/**
 * Edits the field properties of a data object.
 *
 * The form reads the initial values from the object, and on a valid submit writes the parsed values back into it.
 * Persisting the object is up to the caller (directly, or via a storage like {@see Storage\ConfigStorage}).
 *
 * Submitted values that are invalid never reach the object: they are kept as raw values in the form, so they can be
 * redisplayed together with the error.
 *
 * Render the whole form with {@see self::render()}, or build the markup yourself with {@see self::capture()}, using
 * {@see self::row()}/{@see self::widget()} and {@see self::rest()} inside.
 */
final class Form implements Renderable
{
    use RendersView;

    private const string SUBMIT_KEY = '_submit';

    private const string APPLY_KEY = '_apply';

    /** Name of the request parameter holding the submitted values; defaults to the short class name of the data object. */
    public readonly string $name;

    private readonly FormSchema $schema;

    /** @var array<string, FieldDefinition> the fields of the form, keyed by property name */
    private readonly array $fields;

    private bool $submitted = false;

    private bool $applied = false;

    /** @var array<string, string|list<string>> */
    private array $raw = [];

    /** @var array<string, string|Renderable> */
    private array $fieldErrors = [];

    /** @var list<string|Renderable> */
    private array $formErrors = [];

    /** @var array<string, true> */
    private array $rendered = [];

    /** @param list<string>|null $only restricts the form to these properties (in schema order); `null` for all */
    public function __construct(
        public readonly object $data,
        ?string $name = null,
        public string|Renderable|null $title = null,
        public string|Translatable $submitLabel = new Translatable('form_save'),
        public readonly bool $csrf = true,
        ?array $only = null,
        /** Whether the rows place their labels above the widgets instead of next to them, e.g. in narrow columns. */
        public bool $vertical = false,
        /**
         * Label of a second submit button for saving without leaving the form, see {@see self::isApplied()};
         * `null` for none.
         */
        public string|Translatable|null $applyLabel = null,
        /** Target of a cancel link next to the submit buttons; `null` for none. */
        public ?string $cancelUrl = null,
    ) {
        $class = new ReflectionClass($data);
        if (null === $name && $class->isAnonymous()) {
            throw new LogicException('A form for an anonymous class needs an explicit name.');
        }
        $this->name = $name ?? lcfirst($class->getShortName());
        $this->schema = FormSchema::of($data::class);

        $fields = $this->schema->fields;
        if (null !== $only) {
            foreach ($only as $property) {
                $this->schema->get($property);
            }
            $fields = array_intersect_key($fields, array_flip($only));
        }
        $this->fields = $fields;

        foreach ($this->fields as $property => $definition) {
            $this->raw[$property] = $definition->field->toRaw($definition->getValue($data));
        }
    }

    /** Whether the form has been submitted, either in the current request or via {@see self::submit()}. */
    public function isSubmitted(): bool
    {
        return $this->submitted || null !== $this->submittedValues();
    }

    /**
     * Reads the submitted values from the request and, if all of them are valid, writes them into the data object.
     *
     * Only for standalone forms, rendered via {@see self::open()}: the request is recognized by the hidden marker and
     * protected by a csrf token. A form embedded into another form passes the values to {@see self::submit()} itself.
     *
     * @return bool whether the form was submitted and is valid
     */
    public function handleRequest(): bool
    {
        $submitted = $this->submittedValues();

        if (null === $submitted) {
            return false;
        }

        if ($this->csrf && !$this->csrfToken()->isValid()) {
            $this->submitted = true;
            $this->formErrors[] = new Translatable('csrf_token_invalid');

            return false;
        }

        return $this->submit($submitted);
    }

    /**
     * Binds the given submitted raw values (keyed by property name) and, if all of them are valid, writes them into the
     * data object.
     *
     * @param array<array-key, mixed> $submitted
     * @return bool whether all values are valid
     */
    public function submit(array $submitted): bool
    {
        $this->submitted = true;
        $this->applied = isset($submitted[self::APPLY_KEY]);

        $values = [];
        foreach ($this->fields as $name => $definition) {
            $raw = self::normalizeRaw($submitted[$name] ?? null);
            $this->raw[$name] = $raw ?? '';

            $missing = null === $raw || '' === $raw || [] === $raw;

            try {
                $value = $definition->field->fromRaw($raw, $definition->type);
            } catch (InvalidValueException $e) {
                $this->fieldErrors[$name] = $definition->field->required && $missing ? new Translatable('form_required') : $e->userMessage;
                continue;
            }

            if ($definition->field->required && $definition->field->isEmpty($value)) {
                $this->fieldErrors[$name] = new Translatable('form_required');
                continue;
            }

            $values[$name] = $value;
        }

        if ($this->fieldErrors) {
            return false;
        }

        foreach ($values as $name => $value) {
            $this->fields[$name]->setValue($this->data, $value);
        }

        return true;
    }

    /** Whether the form was submitted by the apply button, i.e. the user wants to stay on the form after saving. */
    public function isApplied(): bool
    {
        return $this->applied;
    }

    /** Whether the form was submitted without errors. */
    public function isValid(): bool
    {
        return $this->submitted && !$this->fieldErrors && !$this->formErrors;
    }

    /**
     * Adds an error found after {@see self::handleRequest()}, e.g. by a cross-field check or the save routine.
     *
     * @param string|null $field the property name, or `null` for an error concerning the whole form
     */
    public function addError(string|Renderable $message, ?string $field = null): void
    {
        if (null === $field) {
            $this->formErrors[] = $message;

            return;
        }

        $this->definition($field);
        $this->fieldErrors[$field] = $message;
    }

    /** @return list<string|Renderable> */
    public function getFormErrors(): array
    {
        return $this->formErrors;
    }

    /** @return array<string, string|Renderable> keyed by property name */
    public function getFieldErrors(): array
    {
        return $this->fieldErrors;
    }

    /** Whether the form contains the given property. */
    public function has(string $name): bool
    {
        return isset($this->fields[$name]);
    }

    public function field(string $name): BoundField
    {
        $definition = $this->definition($name);

        return new BoundField(
            $name,
            $definition->field,
            $definition->type,
            $this->name . '[' . $name . ']',
            (string) preg_replace('/[^\w-]/', '_', $this->name . '-' . $name),
            $this->raw[$name],
            $this->fieldErrors[$name] ?? null,
        );
    }

    /** Renders the complete form group of the field (label, widget, note, error). */
    public function row(string $name): Html
    {
        $field = $this->field($name);
        $this->rendered[$name] = true;

        $row = $field->field->row($field);
        if ($this->vertical && $row instanceof FieldRow) {
            $row->vertical = true;
        }

        return $row->render();
    }

    /** Renders the bare control of the field. */
    public function widget(string $name): Html
    {
        $field = $this->field($name);
        $this->rendered[$name] = true;

        return $field->field->widget($field)->render();
    }

    public function label(string $name): Html
    {
        $field = $this->field($name);

        return Html::raw('<label for="' . escape($field->id) . '">' . (string) Html::from($field->field->label) . '</label>');
    }

    /** Renders the rows of all fields not rendered yet, wrapped in their {@see Fieldset}s. */
    public function rest(): Html
    {
        /** @var list<array{fieldset: Fieldset|null, rows: list<Html>}> $groups */
        $groups = [];
        foreach ($this->fields as $name => $definition) {
            if (isset($this->rendered[$name])) {
                continue;
            }

            $last = array_key_last($groups);
            if (null === $last || $groups[$last]['fieldset'] !== $definition->fieldset) {
                $groups[] = ['fieldset' => $definition->fieldset, 'rows' => []];
                $last = array_key_last($groups);
            }
            $groups[$last]['rows'][] = $this->row($name);
        }

        $html = '';
        foreach ($groups as $group) {
            $rows = Html::raw(implode('', $group['rows']));
            $html .= (string) (null === $group['fieldset'] ? $rows : new FieldGroup($rows, $group['fieldset']->legend)->render());
        }

        return Html::raw($html);
    }

    /**
     * Renders the form tag around the markup the closure emits, for custom markup of the form.
     *
     * The closure is called with the form and writes its markup like a view, leaving PHP mode inside.
     *
     * @param Closure(self):void $content
     * @param array<literal-string, bool|string|int|array<string|int, string|bool>|null> $attributes attributes of the form tag
     */
    public function capture(Closure $content, array $attributes = []): Html
    {
        $inner = Html::capture(function () use ($content): void {
            $content($this);
        });

        return Html::raw((string) $this->open($attributes) . (string) $inner . (string) $this->close());
    }

    /**
     * Renders the opening form tag plus the hidden fields the form needs.
     *
     * Prefer {@see self::capture()}; use this together with {@see self::close()} only if the form tag can not be
     * rendered in one place.
     *
     * @param array<literal-string, bool|string|int|array<string|int, string|bool>|null> $attributes
     */
    public function open(array $attributes = []): Html
    {
        // without an action the browser submits to the current url, including all of its parameters
        $attributes = new HtmlAttributes(['method' => 'post'])->with($attributes);

        $html = '<form ' . $attributes->toString() . '>';
        $html .= '<input type="hidden" name="' . escape($this->name . '[' . self::SUBMIT_KEY . ']') . '" value="1">';
        if ($this->csrf) {
            $html .= $this->csrfToken()->getHiddenField();
        }

        return Html::raw($html);
    }

    public function close(): Html
    {
        return Html::raw('</form>');
    }

    public function submitButton(): Html
    {
        $label = Translatable::text($this->submitLabel);

        return Html::raw('<button class="btn btn-save rex-form-aligned" type="submit"' . Accesskey::attributes($label, 'save') . '>' . escape($label) . '</button>');
    }

    /** Renders the apply button, or nothing if there is no {@see self::$applyLabel}. */
    public function applyButton(): Html
    {
        if (null === $this->applyLabel) {
            return Html::raw('');
        }

        $label = Translatable::text($this->applyLabel);

        return Html::raw('<button class="btn btn-apply" type="submit" name="' . escape($this->name . '[' . self::APPLY_KEY . ']') . '" value="1"' . Accesskey::attributes($label, 'apply') . '>' . escape($label) . '</button>');
    }

    /** Renders the cancel link, or nothing if there is no {@see self::$cancelUrl}. */
    public function cancelLink(): Html
    {
        if (null === $this->cancelUrl) {
            return Html::raw('');
        }

        return Html::raw('<a class="btn btn-abort" href="' . escape($this->cancelUrl) . '">' . escape(new Translatable('form_abort')->translate()) . '</a>');
    }

    /** @return array<array-key, mixed>|null */
    private function submittedValues(): ?array
    {
        /** @var array<array-key, mixed> $values */
        $values = Request::post($this->name, 'array', []);

        return array_key_exists(self::SUBMIT_KEY, $values) ? $values : null;
    }

    private function definition(string $name): FieldDefinition
    {
        return $this->fields[$name] ?? throw new LogicException(sprintf('Unknown form field "%s".', $name));
    }

    private function csrfToken(): CsrfToken
    {
        return CsrfToken::factory('form:' . $this->name);
    }

    /** @return string|list<string>|null */
    private static function normalizeRaw(mixed $raw): string|array|null
    {
        if (is_string($raw)) {
            return $raw;
        }

        if (!is_array($raw)) {
            return null;
        }

        $values = [];
        foreach (array_keys($raw) as $key) {
            if (is_string($raw[$key])) {
                $values[] = $raw[$key];
            }
        }

        return $values;
    }
}
