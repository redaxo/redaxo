<?php

namespace Redaxo\Core\Tests\Form;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Exception\LogicException;
use Redaxo\Core\Form\Field\Text;
use Redaxo\Core\Form\Form;
use Redaxo\Core\Tests\Form\Fixtures\Color;
use Redaxo\Core\Tests\Form\Fixtures\Fieldsets;
use Redaxo\Core\Tests\Form\Fixtures\Settings;
use Redaxo\Core\Translation\Translatable;

/** @internal */
final class FormTest extends TestCase
{
    #[Override]
    protected function tearDown(): void
    {
        $_POST = [];
    }

    public function testNotSubmitted(): void
    {
        $form = new Form(new Settings(), csrf: false);

        self::assertSame('settings', $form->name);
        self::assertFalse($form->isSubmitted());
        self::assertFalse($form->handleRequest());
        self::assertSame('Hello', $form->field('title')->raw);
        self::assertSame('1', $form->field('active')->raw);
        self::assertSame('settings[title]', $form->field('title')->inputName);
        self::assertSame('settings-title', $form->field('title')->id);
    }

    public function testValidSubmit(): void
    {
        $_POST['settings'] = ['_submit' => '1', 'title' => 'New', 'subtitle' => '', 'size' => '2', 'color' => 'blue'];

        $settings = new Settings();
        $form = new Form($settings, csrf: false);

        self::assertTrue($form->handleRequest());
        self::assertTrue($form->isValid());
        self::assertSame('New', $settings->title);
        self::assertNull($settings->subtitle);
        self::assertSame(2, $settings->size);
        self::assertSame(Color::Blue, $settings->color);
        self::assertFalse($settings->active, 'a checkbox missing in the request is unchecked');
        self::assertSame('untouched', $settings->notAField);
    }

    /** @param array<string, string> $submitted */
    #[DataProvider('provideInvalidSubmit')]
    public function testInvalidSubmit(string $expectedField, string $expectedError, array $submitted): void
    {
        $_POST['settings'] = $submitted + ['_submit' => '1', 'title' => 'New', 'size' => '1', 'color' => ''];

        $settings = new Settings();
        $form = new Form($settings, csrf: false);

        self::assertFalse($form->handleRequest());
        self::assertFalse($form->isValid());

        $error = $form->field($expectedField)->error;
        self::assertInstanceOf(Translatable::class, $error);
        self::assertSame($expectedError, $error->key);

        self::assertSame('Hello', $settings->title, 'nothing is written into the object');
        self::assertSame($submitted[$expectedField], $form->field($expectedField)->raw, 'the submitted value is kept for redisplay');
    }

    /** @return iterable<string, array{string, string, array<string, string>}> */
    public static function provideInvalidSubmit(): iterable
    {
        yield 'required' => ['title', 'form_required', ['title' => '']];
        yield 'too long' => ['title', 'form_max_length', ['title' => str_repeat('ä', 21)]];
        yield 'unknown choice' => ['size', 'form_invalid_choice', ['size' => '3']];
        yield 'unknown enum choice' => ['color', 'form_invalid_choice', ['color' => 'green']];
    }

    public function testAddError(): void
    {
        $_POST['settings'] = ['_submit' => '1', 'title' => 'New', 'size' => '1'];

        $form = new Form(new Settings(), csrf: false);

        self::assertTrue($form->handleRequest());

        $form->addError('Already taken', 'title');

        self::assertFalse($form->isValid());
        self::assertSame('Already taken', $form->field('title')->error);
    }

    public function testRender(): void
    {
        $form = new Form(new Settings(), csrf: false);

        $html = (string) $form->row('title');
        self::assertStringContainsString('<label for="settings-title">Title</label>', $html);
        self::assertStringContainsString('name="settings[title]"', $html);
        self::assertStringContainsString('value="Hello"', $html);
        self::assertStringContainsString('maxlength="20"', $html);

        $rest = (string) $form->rest();
        self::assertStringNotContainsString('settings[title]', $rest, 'rest() skips already rendered fields');
        self::assertStringContainsString('<option value="1" selected>Small</option>', $rest);
        self::assertStringContainsString('<option value="red">Red</option>', $rest);
        self::assertMatchesRegularExpression('/<input type="checkbox" name="settings\[active\]"[^>]* checked/', $rest);
    }

    public function testSubmitEmbedded(): void
    {
        $settings = new Settings();
        $form = new Form($settings, name: 'meta', only: ['title', 'size']);

        self::assertFalse($form->isSubmitted());
        self::assertTrue($form->submit(['title' => 'Embedded', 'size' => '2', 'active' => '']));
        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid());
        self::assertSame('Embedded', $settings->title);
        self::assertSame(2, $settings->size);
        self::assertTrue($settings->active, 'fields outside of `only` are left untouched');

        self::assertSame('meta[title]', $form->field('title')->inputName);

        $html = (string) $form->rest();
        self::assertStringContainsString('meta[size]', $html);
        self::assertStringNotContainsString('meta[active]', $html);
        self::assertStringNotContainsString('<form', $html);
    }

    public function testAnonymousClass(): void
    {
        $data = new class {
            #[Text('Title')]
            public string $title = '';
        };

        $form = new Form($data, name: 'inline', csrf: false);
        self::assertTrue($form->submit(['title' => 'Inline']));
        self::assertSame('Inline', $data->title);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('A form for an anonymous class needs an explicit name.');
        new Form($data);
    }

    public function testUnknownField(): void
    {
        $form = new Form(new Settings(), only: ['title']);

        $this->expectException(LogicException::class);
        $form->field('size');
    }

    public function testFieldsets(): void
    {
        $form = new Form(new Fieldsets(), csrf: false);

        $html = (string) $form->rest();

        self::assertMatchesRegularExpression('/name="fieldsets\\[a\\]".*<fieldset>\s*<legend>Group 1<\/legend>.*name="fieldsets\\[b\\]".*name="fieldsets\\[c\\]".*<\/fieldset>\s*<fieldset>\s*<legend>Group 2<\/legend>.*name="fieldsets\\[d\\]".*<\/fieldset>/s', $html);

        $html = (string) new Form(new Fieldsets(), only: ['a', 'c'])->rest();
        self::assertMatchesRegularExpression('/<legend>Group 1<\/legend>.*name="fieldsets\\[c\\]"/s', $html, 'a field stays in its group even if the field starting the group is left out');
    }
}
