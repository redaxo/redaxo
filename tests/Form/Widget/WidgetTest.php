<?php

namespace Redaxo\Core\Tests\Form\Widget;

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Form\FieldRow;
use Redaxo\Core\Form\Widget\CheckboxInput;
use Redaxo\Core\Form\Widget\Input;
use Redaxo\Core\Form\Widget\SelectInput;
use Redaxo\Core\View\HtmlAttributes;

/** @internal */
final class WidgetTest extends TestCase
{
    public function testInput(): void
    {
        $html = (string) new Input('foo', 'a "b"', 'foo-id', attributes: new HtmlAttributes(['class' => ['custom'], 'placeholder' => 'Foo']))->render();

        self::assertStringContainsString('class="form-control custom"', $html);
        self::assertStringContainsString('name="foo"', $html);
        self::assertStringContainsString('value="a &quot;b&quot;"', $html);
        self::assertStringContainsString('placeholder="Foo"', $html);
    }

    public function testSelectInput(): void
    {
        $html = (string) new SelectInput('size', [1 => 'Small', 2 => 'Large <XL>'], '2', placeholder: '')->render();

        self::assertStringContainsString('<option value=""></option>', $html);
        self::assertStringContainsString('<option value="1">Small</option>', $html);
        self::assertStringContainsString('<option value="2" selected>Large &lt;XL&gt;</option>', $html);
    }

    public function testCheckboxInputInFieldRow(): void
    {
        $html = (string) new FieldRow(new CheckboxInput('active', 'Active', checked: true), error: 'Wrong')->render();

        self::assertStringNotContainsString('<dt>', $html, 'the checkbox renders its label itself');
        self::assertMatchesRegularExpression('/<label>\s*<input type="checkbox" name="active" value="1" checked>\s*Active\s*<\/label>/', $html);
        self::assertStringContainsString('has-error', $html);
        self::assertStringContainsString('<p class="rex-form-error">Wrong</p>', $html);
    }
}
