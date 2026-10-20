<?php

namespace Redaxo\Core\Tests\Form;

use Override;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Form\Field\Choice;
use Redaxo\Core\Form\Storage\DbStorage;
use Redaxo\Core\Tests\Form\Fixtures\Color;
use Redaxo\Core\Tests\Form\Fixtures\Settings;

/** @internal */
final class DbStorageTest extends TestCase
{
    private const string TABLE = 'rex_tests_form_storage';

    #[Override]
    protected function setUp(): void
    {
        $sql = Sql::factory();
        $sql->setQuery('DROP TABLE IF EXISTS `' . self::TABLE . '`');
        $sql->setQuery('CREATE TABLE `' . self::TABLE . '` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL,
            `subtitle` VARCHAR(255) NULL,
            `size_id` INT NOT NULL,
            `color` VARCHAR(255) NULL,
            `active` TINYINT(1) NOT NULL,
            `createdate` DATETIME NULL,
            `createuser` VARCHAR(255) NULL,
            `updatedate` DATETIME NULL,
            `updateuser` VARCHAR(255) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE = InnoDB');
    }

    #[Override]
    protected function tearDown(): void
    {
        Sql::factory()->setQuery('DROP TABLE IF EXISTS `' . self::TABLE . '`');
    }

    public function testInsertUpdateAndLoad(): void
    {
        $settings = new Settings();
        $settings->title = 'Stored';
        $settings->size = 2;
        $settings->color = Color::Red;
        $settings->active = false;

        $storage = new DbStorage(self::TABLE, globalFields: true);
        $storage->save($settings);

        self::assertIsInt($storage->id);
        $row = Sql::factory()->getArray('SELECT * FROM ' . self::TABLE)[0];
        self::assertSame('Stored', $row['title']);
        self::assertSame(2, $row['size_id'], 'the field key is used as column');
        self::assertSame('red', $row['color']);
        self::assertSame(0, $row['active']);
        self::assertNotNull($row['createdate']);
        self::assertNotNull($row['updatedate']);

        $settings->title = 'Updated';
        $storage->save($settings);

        self::assertSame(1, Sql::factory()->getArray('SELECT COUNT(*) AS c FROM ' . self::TABLE)[0]['c']);

        $loaded = new Settings();
        self::assertTrue(new DbStorage(self::TABLE, $storage->id)->load($loaded));
        self::assertEquals($settings, $loaded);
    }

    public function testLoadMissingRow(): void
    {
        self::assertFalse(new DbStorage(self::TABLE)->load(new Settings()));
        self::assertFalse(new DbStorage(self::TABLE, 999)->load(new Settings()));
    }

    public function testArrayAsJson(): void
    {
        Sql::factory()->setQuery('ALTER TABLE `' . self::TABLE . '` ADD `tags` TEXT NULL');

        $data = new class {
            /** @var list<string> */
            #[Choice(
                'Tags',
                choices: ['a' => 'A', 'b' => 'B'],
                multiple: true,
            )]
            public array $tags = [];
        };
        $data->tags = ['a', 'b'];

        $sql = Sql::factory();
        $sql->setQuery('INSERT INTO `' . self::TABLE . '` (title, size_id, active) VALUES ("", 1, 1)');
        $storage = new DbStorage(self::TABLE, $sql->getLastId());
        $storage->save($data);

        self::assertSame('["a","b"]', Sql::factory()->getArray('SELECT tags FROM ' . self::TABLE)[0]['tags']);

        $loaded = new ($data::class)();
        $storage->load($loaded);
        self::assertSame(['a', 'b'], $loaded->tags);

        Sql::factory()->setQuery('UPDATE `' . self::TABLE . '` SET tags = "no json"');
        $storage->load($loaded);
        self::assertSame(['a', 'b'], $loaded->tags, 'invalid values are skipped');
    }
}
