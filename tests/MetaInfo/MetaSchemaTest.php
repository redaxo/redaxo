<?php

namespace Redaxo\Core\Tests\MetaInfo;

use BcMath\Number as Decimal;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Database\Column;
use Redaxo\Core\Form\Field\Checkbox;
use Redaxo\Core\Form\Field\Choice;
use Redaxo\Core\Form\Field\DatePicker;
use Redaxo\Core\Form\Field\Number;
use Redaxo\Core\Form\Field\Text;
use Redaxo\Core\Form\Field\Textarea;
use Redaxo\Core\Form\Field\Url;
use Redaxo\Core\MetaInfo\AsMetaSchema;
use Redaxo\Core\MetaInfo\MetaContext;
use Redaxo\Core\MetaInfo\MetaEntity;
use Redaxo\Core\MetaInfo\MetaField;
use Redaxo\Core\MetaInfo\MetaSchema;
use Redaxo\Core\MetaInfo\Translated;
use Redaxo\Core\Tests\Form\Fixtures\Color;

use function array_key_exists;

/** @internal */
final class MetaSchemaTest extends TestCase
{
    /** @return class-string<MetaSchema> */
    private static function schema(): string
    {
        // anonymous, so that the class discovery does not find it and migrate does not create its columns
        return (new #[AsMetaSchema(MetaEntity::Article)] class extends MetaSchema {
            #[Text(
                'SEO title',
                maxLength: 100,
            )]
            #[Translated]
            public string $seoTitle = '';

            #[Textarea('Description')]
            #[Translated]
            public ?string $description = null;

            #[Checkbox('No index')]
            public bool $noIndex = false;

            #[DatePicker('Published')]
            public ?DateTimeImmutable $publishDate = null;

            /** @var list<string> */
            #[Choice(
                'Tags',
                choices: ['a' => 'A', 'b' => 'B'],
                multiple: true,
            )]
            public array $tags = [];

            #[Choice(
                'Color',
                choices: ['red' => 'Red', 'blue' => 'Blue'],
            )]
            public ?Color $color = null;

            #[Choice(
                'Priority',
                choices: [1 => 'Low', 2 => 'High'],
            )]
            public int $priority = 1;

            #[Number('Weight')]
            public ?float $weight = null;

            #[Text('Custom')]
            public string $custom = '';

            public static function column(string $property, string $name): ?Column
            {
                return 'custom' === $property ? Column::varchar($name, 20, nullable: true) : null;
            }

            public static function isFieldAllowed(string $property, MetaContext $context): bool
            {
                return 'noIndex' !== $property || null !== $context->category;
            }
        })::class;
    }

    public function testColumns(): void
    {
        $fields = self::fields();

        self::assertSame(
            ['seoTitle' => 'meta_seo_title', 'description' => 'meta_description', 'noIndex' => 'meta_no_index', 'publishDate' => 'meta_publish_date', 'tags' => 'meta_tags', 'color' => 'meta_color', 'priority' => 'meta_priority', 'weight' => 'meta_weight', 'custom' => 'meta_custom'],
            array_map(static fn (MetaField $field): string => $field->column, $fields),
        );

        self::assertSame('rex_article_translation', $fields['seoTitle']->table());
        self::assertSame('rex_article', $fields['noIndex']->table());

        self::assertEquals(Column::varchar('meta_seo_title', 100, nullable: true), $fields['seoTitle']->columnDefinition());
        self::assertEquals(Column::text('meta_description', nullable: true), $fields['description']->columnDefinition());
        self::assertEquals(Column::bool('meta_no_index', default: false), $fields['noIndex']->columnDefinition());
        self::assertEquals(Column::date('meta_publish_date', nullable: true), $fields['publishDate']->columnDefinition());
        self::assertEquals(Column::text('meta_tags', nullable: true), $fields['tags']->columnDefinition());
        self::assertEquals(Column::varchar('meta_color', 255, nullable: true), $fields['color']->columnDefinition());
        self::assertEquals(Column::int('meta_priority', nullable: true), $fields['priority']->columnDefinition());
        self::assertEquals(Column::double('meta_weight', nullable: true), $fields['weight']->columnDefinition());
        self::assertEquals(Column::varchar('meta_custom', 20, nullable: true), $fields['custom']->columnDefinition(), 'the schema overrides the column');
    }

    public function testToColumn(): void
    {
        $fields = self::fields();

        self::assertSame('Title', $fields['seoTitle']->toColumn('Title'));
        self::assertNull($fields['description']->toColumn(null));
        self::assertSame(1, $fields['noIndex']->toColumn(true));
        self::assertSame('2026-10-20', $fields['publishDate']->toColumn(new DateTimeImmutable('2026-10-20 12:00')));
        self::assertSame('a,b', $fields['tags']->toColumn(['a', 'b']));
        self::assertSame('', $fields['tags']->toColumn([]));
        self::assertSame('blue', $fields['color']->toColumn(Color::Blue));
        self::assertSame(2, $fields['priority']->toColumn(2));
    }

    public function testLoad(): void
    {
        $meta = self::schema()::load(self::subject([
            'meta_seo_title' => 'Title',
            'meta_description' => null,
            'meta_no_index' => '1',
            'meta_publish_date' => '2026-10-20',
            'meta_tags' => 'a,b',
            'meta_color' => 'blue',
            'meta_priority' => '2',
            'meta_weight' => '1.5',
            'meta_custom' => 'x',
        ]));

        self::assertEquals([
            'seoTitle' => 'Title',
            'description' => null,
            'noIndex' => true,
            'publishDate' => new DateTimeImmutable('2026-10-20 00:00'),
            'tags' => ['a', 'b'],
            'color' => Color::Blue,
            'priority' => 2,
            'weight' => 1.5,
            'custom' => 'x',
        ], get_object_vars($meta));
    }

    public function testLoadKeepsDefaultsForMissingAndInvalidValues(): void
    {
        $meta = self::schema()::load(self::subject([
            'meta_seo_title' => null,
            'meta_publish_date' => 'invalid',
            'meta_tags' => '',
            'meta_color' => 'green',
            'meta_priority' => 'high',
        ]));

        self::assertEquals([
            'seoTitle' => '',
            'description' => null,
            'noIndex' => false,
            'publishDate' => null,
            'tags' => [],
            'color' => null,
            'priority' => 1,
            'weight' => null,
            'custom' => '',
        ], get_object_vars($meta), 'missing and invalid values keep the defaults');
    }

    public function testColumnFromKey(): void
    {
        $class = new #[AsMetaSchema(MetaEntity::Article)] class extends MetaSchema {
            #[Text(
                'Renamed',
                key: 'old_name',
            )]
            public string $newName = '';
        }::class;

        self::assertSame('meta_old_name', MetaSchema::fieldsOf($class)[0]->column);
    }

    public function testTextColumnWithoutMaxLength(): void
    {
        $class = new #[AsMetaSchema(MetaEntity::Article)] class extends MetaSchema {
            #[Url('Website')]
            public ?string $website = null;
        }::class;

        self::assertEquals(Column::varchar('meta_website', 255, nullable: true), MetaSchema::fieldsOf($class)[0]->columnDefinition());
    }

    #[RequiresPhpExtension('bcmath')]
    public function testDecimal(): void
    {
        $class = new #[AsMetaSchema(MetaEntity::Article)] class extends MetaSchema {
            #[Number(
                'Price',
                step: '0.05',
            )]
            public ?Decimal $price = null;
        }::class;

        $field = MetaSchema::fieldsOf($class)[0];
        self::assertEquals(Column::decimal('meta_price', 20, 2, nullable: true), $field->columnDefinition());
        self::assertSame('9.95', $field->toColumn(new Decimal('9.95')));

        $meta = $class::load(self::subject(['meta_price' => '9.95']));
        self::assertEquals(['price' => new Decimal('9.95')], get_object_vars($meta));
    }

    public function testLoadWithoutSubject(): void
    {
        $class = self::schema();
        self::assertEquals(new $class(), $class::load(null));
    }

    /** @return array<string, MetaField> */
    private static function fields(): array
    {
        $fields = [];
        foreach (MetaSchema::fieldsOf(self::schema()) as $field) {
            $fields[$field->property] = $field;
        }

        return $fields;
    }

    /** @param array<string, string|null> $values */
    private static function subject(array $values): object
    {
        return new readonly class($values) {
            /** @param array<string, string|null> $values */
            public function __construct(
                private array $values,
            ) {}

            public function hasValue(string $column): bool
            {
                return array_key_exists($column, $this->values);
            }

            public function getValue(string $column): ?string
            {
                return $this->values[$column];
            }
        };
    }
}
