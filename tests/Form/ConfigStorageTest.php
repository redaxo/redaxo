<?php

namespace Redaxo\Core\Tests\Form;

use Override;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Config;
use Redaxo\Core\Form\Field\Number;
use Redaxo\Core\Form\Field\Text;
use Redaxo\Core\Form\Storage\ConfigStorage;
use Redaxo\Core\Tests\Form\Fixtures\Color;
use Redaxo\Core\Tests\Form\Fixtures\Settings;

/** @internal */
final class ConfigStorageTest extends TestCase
{
    private const string CONFIG_NAMESPACE = 'test-form-storage';

    #[Override]
    protected function tearDown(): void
    {
        Config::removeNamespace(self::CONFIG_NAMESPACE);
    }

    public function testSaveAndLoad(): void
    {
        $settings = new Settings();
        $settings->title = 'Stored';
        $settings->size = 2;
        $settings->color = Color::Red;
        $settings->active = false;

        $storage = new ConfigStorage(self::CONFIG_NAMESPACE);
        $storage->save($settings);

        self::assertSame('Stored', Config::get(self::CONFIG_NAMESPACE, 'title'));
        self::assertSame(2, Config::get(self::CONFIG_NAMESPACE, 'size_id'), 'the field key is used');
        self::assertSame('red', Config::get(self::CONFIG_NAMESPACE, 'color'));
        self::assertFalse(Config::has(self::CONFIG_NAMESPACE, 'notAField'));

        $loaded = new Settings();
        $storage->load($loaded);

        self::assertEquals($settings, $loaded);
    }

    public function testKeysInSnakeCase(): void
    {
        $data = new class {
            #[Number('Max items')]
            public int $maxItems = 10;

            #[Text('HTML title')]
            public string $HTMLTitle = '';
        };
        $data->maxItems = 20;
        $data->HTMLTitle = 'Title';

        new ConfigStorage(self::CONFIG_NAMESPACE)->save($data);

        self::assertSame(['max_items' => 20, 'html_title' => 'Title'], Config::get(self::CONFIG_NAMESPACE));
    }

    public function testLoadSkipsMissingAndInvalidValues(): void
    {
        Config::set(self::CONFIG_NAMESPACE, ['title' => 'Stored', 'size_id' => 'large', 'color' => 'green']);

        $settings = new Settings();
        new ConfigStorage(self::CONFIG_NAMESPACE)->load($settings);

        self::assertSame('Stored', $settings->title);
        self::assertSame(1, $settings->size);
        self::assertNull($settings->color);
        self::assertTrue($settings->active);
    }
}
