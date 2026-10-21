<?php

namespace Redaxo\Core\MetaInfo;

use Redaxo\Core\Database\Sql;
use Redaxo\Core\Exception\LogicException;
use Redaxo\Core\Form\Form;
use Redaxo\Core\Http\Request;
use Redaxo\Core\View\Html;
use Redaxo\Core\View\Message;

/**
 * The meta fields of an item, embedded into the form of the item.
 *
 * Each {@see MetaSchema} having fields visible in the context is edited by its own {@see Form}, all of them submitting
 * their values below {@see self::REQUEST_KEY}. The host validates them via {@see self::submit()} before saving the
 * item, and hands the instance to the save routine of the item, which writes the values via {@see self::apply()}.
 *
 * @internal
 */
final class MetaForm
{
    /** Name of the request parameter holding the submitted meta values. */
    public const string REQUEST_KEY = 'meta';

    /** @var list<Form> */
    private readonly array $forms;

    /** `null` as long as not submitted. */
    private ?bool $valid = null;

    public function __construct(
        public readonly MetaContext $context,
    ) {
        $forms = [];
        foreach (MetaSchema::getSchemas($context->entity) as $class) {
            $only = [];
            foreach (MetaSchema::fieldsOf($class) as $field) {
                if ($class::isFieldAllowed($field->property, $context)) {
                    $only[] = $field->property;
                }
            }

            if ($only) {
                $forms[] = new Form($class::load($context->subject), name: self::REQUEST_KEY, only: $only);
            }
        }
        $this->forms = $forms;
    }

    /** Whether the schemas define at least one field visible in the context. */
    public function hasFields(): bool
    {
        return [] !== $this->forms;
    }

    /**
     * Binds the submitted values.
     *
     * @return bool whether all values are valid; only then they may be saved via {@see self::apply()}
     */
    public function submit(): bool
    {
        /** @var array<array-key, mixed> $values */
        $values = Request::post(self::REQUEST_KEY, 'array', []);

        $valid = true;
        foreach ($this->forms as $form) {
            if (!$form->submit($values)) {
                $valid = false;
                continue;
            }

            /** @var MetaSchema $schema */
            $schema = $form->data;
            $error = $schema->validate();
            if (null !== $error) {
                $form->addError($error);
                $valid = false;
            }
        }

        return $this->valid = $valid;
    }

    /** Whether the submitted values are invalid. */
    public function hasErrors(): bool
    {
        return false === $this->valid;
    }

    /**
     * Renders the fields, with the submitted values and their errors after a submit.
     *
     * @param bool $vertical whether to place the labels above the fields, e.g. in narrow columns
     */
    public function render(bool $vertical = false): string
    {
        $html = '';
        foreach ($this->forms as $form) {
            $form->vertical = $vertical;
            foreach ($form->getFormErrors() as $error) {
                $html .= Message::error((string) Html::from($error));
            }
            $html .= (string) $form->rest();
        }

        return $html;
    }

    /**
     * Sets the submitted values of the fields stored in the given table on the save object of the item.
     *
     * @param non-empty-string $table the main or the translation table of the entity
     */
    public function apply(Sql $sql, string $table): void
    {
        if (true !== $this->valid) {
            throw new LogicException('Only valid submitted meta values can be saved.');
        }

        foreach ($this->forms as $form) {
            /** @var MetaSchema $schema */
            $schema = $form->data;

            foreach (MetaSchema::fieldsOf($schema::class) as $field) {
                if ($form->has($field->property) && $field->table() === $table) {
                    $sql->setValue($field->column, $field->toColumn($field->definition->getValue($schema)));
                }
            }
        }
    }
}
