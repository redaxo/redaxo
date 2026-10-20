<?php

namespace Redaxo\Core\Tests\Form;

use Override;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Config;
use Redaxo\Core\Form\Storage\ConfigStorage;
use Redaxo\Core\Tests\Form\Fixtures\Color;
use Redaxo\Core\Tests\Form\Fixtures\Settings;

/** @internal */
final class ConfigStorageTest extends TestCase
{
    private const string NAMESPACE = 'test-form-storage';

    #[Override]
    protected function tearDown(): void
    {
        Config::removeNamespace(self::NAMESPACE);
    }

    public function testSaveAndLoad(): void
    {
        $settings = new Settings();
        $settings->title = 'Stored';
        $settings->size = 2;
        $settings->color = Color::Red;
        $settings->active = false;

        $storage = new ConfigStorage(self::NAMESPACE);
        $storage->save($settings);

        self::assertSame('Stored', Config::get(self::NAMESPACE, 'title'));
        self::assertSame(2, Config::get(self::NAMESPACE, 'size_id'), 'the field key is used');
        self::assertSame('red', Config::get(self::NAMESPACE, 'color'));
        self::assertFalse(Config::has(self::NAMESPACE, 'notAField'));

        $loaded = new Settings();
        $storage->load($loaded);

        self::assertEquals($settings, $loaded);
    }

    public function testLoadSkipsMissingAndInvalidValues(): void
    {
        Config::set(self::NAMESPACE, ['title' => 'Stored', 'size_id' => 'large', 'color' => 'green']);

        $settings = new Settings();
        new ConfigStorage(self::NAMESPACE)->load($settings);

        self::assertSame('Stored', $settings->title);
        self::assertSame(1, $settings->size);
        self::assertNull($settings->color);
        self::assertTrue($settings->active);
    }
}
