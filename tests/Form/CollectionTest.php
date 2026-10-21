<?php

namespace Redaxo\Core\Tests\Form;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Form\Form;
use Redaxo\Core\Form\FormSchema;
use Redaxo\Core\Tests\Form\Fixtures\CollectionData;
use Redaxo\Core\Tests\Form\Fixtures\CollectionItem;
use Redaxo\Core\Tests\Form\Fixtures\CollectionTag;
use Redaxo\Core\Translation\Translatable;

/** @internal */
final class CollectionTest extends TestCase
{
    public function testSubmit(): void
    {
        $data = new CollectionData();
        $form = new Form($data, csrf: false);

        // the keys only group the values of an item, the order is the submitted one
        self::assertTrue($form->submit(['items' => [
            '5' => ['title' => 'First', 'itemCount' => '3', 'tags' => [['name' => 'a'], ['name' => 'b']]],
            '0' => ['title' => 'Second', 'itemCount' => ''],
        ]]));

        self::assertCount(2, $data->items);
        self::assertSame('First', $data->items[0]->title);
        self::assertSame(3, $data->items[0]->itemCount);
        self::assertSame(['a', 'b'], array_map(static fn (CollectionTag $tag) => $tag->name, $data->items[0]->tags));
        self::assertSame('Second', $data->items[1]->title);
        self::assertNull($data->items[1]->itemCount);
        self::assertSame([], $data->items[1]->tags);
    }

    /** @param array<array-key, mixed> $items */
    #[DataProvider('provideInvalidSubmit')]
    public function testInvalidSubmit(string $expectedError, array $items): void
    {
        $data = new CollectionData();
        $form = new Form($data, csrf: false);

        self::assertFalse($form->submit(['items' => $items]));

        $error = $form->field('items')->error;
        self::assertInstanceOf(Translatable::class, $error);
        self::assertSame($expectedError, $error->key);
        self::assertSame([], $data->items);
    }

    /** @return iterable<string, array{string, array<array-key, mixed>}> */
    public static function provideInvalidSubmit(): iterable
    {
        yield 'invalid item' => ['form_collection_invalid', [['title' => 'A'], ['title' => '']]];
        yield 'invalid nested item' => ['form_collection_invalid', [['title' => 'A'], ['title' => 'B', 'itemCount' => 'x']]];
        yield 'too few items' => ['form_collection_min', [['title' => 'A']]];
        yield 'too many items' => ['form_collection_max', [['title' => 'A'], ['title' => 'B'], ['title' => 'C'], ['title' => 'D']]];
    }

    public function testEmptyCollectionIgnoresMin(): void
    {
        self::assertTrue(new Form(new CollectionData(), csrf: false)->submit([]));
    }

    public function testEncodeAndHydrate(): void
    {
        $item = new CollectionItem();
        $item->title = 'First';
        $item->itemCount = 3;
        $tag = new CollectionTag();
        $tag->name = 'a';
        $item->tags = [$tag];

        $data = new CollectionData();
        $data->items = [$item];

        $schema = FormSchema::of(CollectionData::class);
        $stored = $schema->encode($data);
        self::assertSame(['items' => [['title' => 'First', 'item_count' => 3, 'tags' => [['name' => 'a']]]]], $stored);

        // items that are no arrays are skipped, values not fitting keep the default
        $stored['items'][] = 'invalid';
        $stored['items'][] = ['title' => 'Second', 'item_count' => 'many'];

        $hydrated = new CollectionData();
        $schema->hydrate($hydrated, $stored);

        self::assertCount(2, $hydrated->items);
        self::assertSame('First', $hydrated->items[0]->title);
        self::assertSame('a', $hydrated->items[0]->tags[0]->name);
        self::assertSame('Second', $hydrated->items[1]->title);
        self::assertNull($hydrated->items[1]->itemCount);
    }

    public function testRender(): void
    {
        $item = new CollectionItem();
        $item->title = 'First';
        $data = new CollectionData();
        $data->items = [$item];

        $html = (string) new Form($data, csrf: false)->row('items');

        self::assertMatchesRegularExpression('/name="collectionData\\[items\\]\\[0\\]\\[title\\]"[^>]* value="First"/', $html);
        self::assertStringContainsString('data-rex-collection-next="1"', $html);
        self::assertStringContainsString('data-rex-collection-max="3"', $html);

        // the placeholder of a nested collection contains the one of the outer collection, so cloning an item keeps
        // the nested template consistent
        self::assertStringContainsString('data-rex-collection-placeholder="__collectionData_items__"', $html);
        self::assertStringContainsString('name="collectionData[items][__collectionData_items__][title]"', $html);
        self::assertMatchesRegularExpression('/data-rex-collection-placeholder="__\w*__collectionData_items__\w*__"/', $html);
    }

    public function testRenderShowsTheErrorsOfInvalidItems(): void
    {
        $form = new Form(new CollectionData(), csrf: false);
        $form->submit(['items' => [['title' => 'A'], ['title' => '', 'itemCount' => '7']]]);

        $html = (string) $form->row('items');

        self::assertMatchesRegularExpression('/name="collectionData\\[items\\]\\[1\\]\\[itemCount\\]"[^>]* value="7"/', $html);
        self::assertStringContainsString(new Translatable('form_required')->translate(), $html);
        self::assertStringContainsString(new Translatable('form_collection_invalid')->translate(), $html);
    }
}
