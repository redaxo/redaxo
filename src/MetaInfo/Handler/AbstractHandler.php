<?php

namespace Redaxo\Core\MetaInfo\Handler;

use Redaxo\Core\Database\Sql;
use Redaxo\Core\Exception\UserMessageException;
use Redaxo\Core\Form\Form;
use Redaxo\Core\Http\Request;
use Redaxo\Core\MetaInfo\MetaContext;
use Redaxo\Core\MetaInfo\MetaEntity;
use Redaxo\Core\MetaInfo\MetaSchema;
use Redaxo\Core\Security\CsrfToken;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\Html;
use Redaxo\Core\View\Message;

use function implode;

/**
 * Renders and saves the meta fields of an entity, driven by its {@see MetaSchema}s.
 *
 * Each schema is edited by a {@see Form} embedded into the form of the entity, all of them submitting their values
 * below the {@see self::REQUEST_KEY}. The forms of a submit are kept for the rest of the request, so that a later
 * rendering shows the submitted values and their errors.
 *
 * @internal
 */
abstract class AbstractHandler
{
    /** Name of the request parameter holding the submitted meta values. */
    public const string REQUEST_KEY = 'meta';

    /** @var array<string, list<Form>> the submitted forms, keyed by entity case name */
    private array $submitted = [];

    /**
     * Csrf token for forms which are provided by metainfo itself.
     *
     * Meta fields which are rendered into a form of another addon are covered by the csrf token of that form.
     */
    public static function getCsrfToken(): CsrfToken
    {
        return CsrfToken::factory('metainfo');
    }

    /** Renders the fields visible in the given context, with the submitted values and errors after a submit. */
    public function renderFields(MetaContext $context): string
    {
        $html = '';
        foreach ($this->submitted[$context->entity->name] ?? $this->createForms($context) as $form) {
            foreach ($form->getFormErrors() as $error) {
                $html .= Message::error((string) Html::from($error));
            }
            $html .= (string) $form->rest();
        }

        return $html;
    }

    /** Whether the schemas define at least one field visible in the given context. */
    public function hasFields(MetaContext $context): bool
    {
        return [] !== $this->createForms($context);
    }

    /**
     * Binds the submitted values of the fields visible in the given context.
     *
     * @return bool whether all values are valid; only then {@see self::saveValues()} may be called
     */
    protected function submit(MetaContext $context): bool
    {
        /** @var array<array-key, mixed> $values */
        $values = Request::post(self::REQUEST_KEY, 'array', []);

        $valid = true;
        $forms = $this->createForms($context);
        foreach ($forms as $form) {
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

        $this->submitted[$context->entity->name] = $forms;

        return $valid;
    }

    /**
     * Sets the values submitted by {@see self::submit()} on the given save object.
     *
     * @param bool|null $translated restrict to the translated (`true`) or the shared (`false`) fields, as both are
     *     saved into different tables; `null` sets all fields
     */
    protected function saveValues(Sql $sql, MetaEntity $entity, ?bool $translated = null): void
    {
        foreach ($this->submitted[$entity->name] ?? [] as $form) {
            /** @var MetaSchema $schema */
            $schema = $form->data;

            foreach (MetaSchema::fieldsOf($schema::class) as $field) {
                if (!$form->has($field->property) || (null !== $translated && $field->translated !== $translated)) {
                    continue;
                }

                $sql->setValue($field->column, $field->toColumn($field->definition->getValue($schema)));
            }
        }
    }

    /**
     * The errors of the submitted values, for hosts that save the meta fields after their own data and show the errors
     * as message instead of rendering the meta fields again.
     */
    protected function invalidValues(MetaEntity $entity): UserMessageException
    {
        $errors = [I18n::msg('minfo_metadata_not_saved')];
        foreach ($this->submitted[$entity->name] ?? [] as $form) {
            foreach ($form->getFormErrors() as $error) {
                $errors[] = (string) Html::from($error);
            }
            foreach ($form->getFieldErrors() as $property => $error) {
                $errors[] = (string) Html::from($form->field($property)->field->label) . ': ' . (string) Html::from($error);
            }
        }

        return new UserMessageException(implode('<br>', $errors));
    }

    /** @return list<Form> the forms of all schemas having fields visible in the given context */
    private function createForms(MetaContext $context): array
    {
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

        return $forms;
    }
}
