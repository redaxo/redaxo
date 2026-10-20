<?php

namespace Redaxo\Core\Tests\Form;

use DateTimeImmutable;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Exception\LogicException;
use Redaxo\Core\Form\Field\ArticlePicker;
use Redaxo\Core\Form\Field\Choice;
use Redaxo\Core\Form\Field\DatePicker;
use Redaxo\Core\Form\Field\Text;
use Redaxo\Core\Form\Form;
use Redaxo\Core\Form\FormSchema;
use Redaxo\Core\Tests\Form\Fixtures\AllFields;
use Redaxo\Core\Tests\Form\Fixtures\Color;
use Redaxo\Core\Translation\Translatable;

/** @internal */
final class FieldsTest extends TestCase
{
    #[Override]
    protected function tearDown(): void
    {
        $_POST = [];
    }

    public function testValidSubmit(): void
    {
        $_POST['allFields'] = [
            '_submit' => '1',
            'text' => "Line 1\nLine 2",
            'date' => '2026-10-19',
            'dateTime' => '2026-10-19T14:30',
            'time' => '08:15',
            'tags' => ['a', 'c'],
            'days' => ['1', '3'],
            'color' => 'blue',
            'article' => '',
            'articles' => '',
        ];

        $data = new AllFields();
        $form = new Form($data, csrf: false);

        self::assertTrue($form->handleRequest());
        self::assertSame("Line 1\nLine 2", $data->text);
        self::assertEquals(new DateTimeImmutable('2026-10-19 00:00:00'), $data->date);
        self::assertSame('2026-10-19 14:30:00', $data->dateTime);
        self::assertEquals(new DateTimeImmutable('1970-01-01 08:15:00'), $data->time);
        self::assertSame(['a', 'c'], $data->tags);
        self::assertSame([1, 3], $data->days);
        self::assertSame(Color::Blue, $data->color);
        self::assertNull($data->article);
        self::assertSame([], $data->articles);
        self::assertSame([], $data->media);
    }

    /** @param array<string, string|list<string>> $submitted */
    #[DataProvider('provideInvalidSubmit')]
    public function testInvalidSubmit(string $expectedField, string $expectedError, array $submitted): void
    {
        $_POST['allFields'] = ['_submit' => '1'] + $submitted;

        $form = new Form(new AllFields(), csrf: false);

        self::assertFalse($form->handleRequest());

        $error = $form->field($expectedField)->error;
        self::assertInstanceOf(Translatable::class, $error);
        self::assertSame($expectedError, $error->key);
    }

    /** @return iterable<string, array{string, string, array<string, string|list<string>>}> */
    public static function provideInvalidSubmit(): iterable
    {
        yield 'invalid date' => ['date', 'form_invalid_value', ['date' => '2026-02-30']];
        yield 'invalid time' => ['time', 'form_invalid_value', ['time' => '25:00']];
        yield 'unknown grouped choice' => ['tags', 'form_invalid_choice', ['tags' => ['a', 'x']]];
        yield 'missing article' => ['article', 'form_article_not_found', ['article' => '999999999']];
        yield 'missing article in list' => ['articles', 'form_article_not_found', ['articles' => '999999999']];
        yield 'missing medium' => ['media', 'form_media_not_found', ['media' => 'missing.jpg']];
    }

    public function testInitialRawValues(): void
    {
        $data = new AllFields();
        $data->date = new DateTimeImmutable('2026-10-19 13:00:00');
        $data->dateTime = '2026-10-19 14:30:00';
        $data->days = [2];

        $form = new Form($data, csrf: false);

        self::assertSame('2026-10-19', $form->field('date')->raw);
        self::assertSame('2026-10-19T14:30', $form->field('dateTime')->raw);
        self::assertSame(['2'], $form->field('days')->raw);
    }

    public function testRender(): void
    {
        $data = new AllFields();
        $data->tags = ['b'];
        $data->days = [2];

        $form = new Form($data, csrf: false);

        $html = (string) $form->row('tags');
        self::assertStringContainsString('name="allFields[tags][]"', $html);
        self::assertStringContainsString('multiple', $html);
        self::assertMatchesRegularExpression('/<optgroup label="Group">\s*<option value="b" selected>B<\/option>\s*<option value="c">C<\/option>\s*<\/optgroup>/', $html);

        $html = (string) $form->row('days');
        self::assertStringNotContainsString('<label for=', $html, 'a checkbox group has no single label target');
        self::assertMatchesRegularExpression('/<input type="checkbox" name="allFields\[days\]\[\]" id="allFields-days-2" value="2" checked>/', $html);

        $html = (string) $form->row('date');
        self::assertStringContainsString('type="date"', $html);
    }

    public function testMissingDefault(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/must have a default value/');

        FormSchema::of(new class {
            #[Text('Title')]
            public string $title;

            public function __construct()
            {
                $this->title = '';
            }
        }::class);
    }

    /** @param class-string $class */
    #[DataProvider('provideUnsupportedType')]
    public function testUnsupportedType(string $class): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/is not supported by field type/');

        FormSchema::of($class);
    }

    /** @return iterable<string, array{class-string}> */
    public static function provideUnsupportedType(): iterable
    {
        yield 'multiple choice without array' => [new class {
            #[Choice('Tags', choices: ['a' => 'A'], multiple: true)]
            public string $tags = '';
        }::class];
        yield 'article picker with int' => [new class {
            #[ArticlePicker('Article')]
            public ?int $article = null;
        }::class];
        yield 'date picker with int' => [new class {
            #[DatePicker('Date')]
            public ?int $date = null;
        }::class];
    }
}
