<?php

use Redaxo\Core\Config;
use Redaxo\Core\Core;
use Redaxo\Core\Cronjob\CronjobManager;
use Redaxo\Core\Database\Column;
use Redaxo\Core\Database\ForeignKey;
use Redaxo\Core\Database\Index;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Database\Table;
use Redaxo\Core\Exception\UserMessageException;
use Redaxo\Core\Filesystem\Dir;
use Redaxo\Core\Filesystem\Path;

// rebuilt from scratch so that files removed from the core don't linger
if (!Dir::delete(Path::coreAssets()) || !Dir::copy(Path::core('assets'), Path::coreAssets())) {
    throw new UserMessageException('Unable to copy the assets to "' . Path::coreAssets() . '". Is the folder writable?');
}

if (!Dir::create(Path::media())) {
    throw new UserMessageException('Unable to create the media folder "' . Path::media() . '". Is the parent folder writable?');
}

Table::get('rex_language')
    ->ensurePrimaryIdColumn()
    ->ensureColumn(Column::varchar('code', 35)) // maximum length of a well-formed BCP 47 language tag
    ->ensureColumn(Column::varchar('name', 255))
    ->ensureColumn(Column::smallint('priority', unsigned: true))
    ->ensureColumn(Column::bool('status'))
    ->ensureIndex(new Index('code', ['code'], Index::UNIQUE))
    ->ensure();

$sql = Sql::factory();
if (!$sql->setQuery('SELECT 1 FROM rex_language LIMIT 1')->getRows()) {
    $sql->setTable('rex_language');
    $sql->setValues(['id' => 1, 'code' => 'de', 'name' => 'deutsch', 'priority' => 1, 'status' => 1]);
    $sql->insert();
}

Table::get('rex_config')
    ->ensureColumn(Column::varchar('namespace', 75))
    ->ensureColumn(Column::varchar('key', 255))
    ->ensureColumn(Column::text('value'))
    ->setPrimaryKey(['namespace', 'key'])
    ->ensure();

Table::get('rex_migration')
    ->ensureColumn(Column::varchar('package', 191))
    ->ensureColumn(Column::varchar('id', 191))
    ->ensureColumn(Column::datetime('executed'))
    ->setPrimaryKey(['package', 'id'])
    ->ensure();

// every structure node is an article; the foreign key of `parent_id` follows below, once the categories exist
Table::get('rex_article')
    ->ensurePrimaryIdColumn()
    ->ensureColumn(Column::int('parent_id', unsigned: true, nullable: true))
    ->ensureColumn(Column::int('priority', unsigned: true))
    ->ensureColumn(Column::varchar('path', 255))
    ->ensureGlobalColumns()
    ->ensure();

Table::get('rex_article_translation')
    ->ensureForeignIdColumn('article_id', 'rex_article', onDelete: ForeignKey::CASCADE)
    ->ensureForeignIdColumn('language_id', 'rex_language', onDelete: ForeignKey::CASCADE)
    ->ensureColumn(Column::varchar('name', 255))
    ->ensureColumn(Column::bool('status'))
    ->ensureColumn(Column::varchar('template', 191, nullable: true))
    ->setPrimaryKey(['article_id', 'language_id'])
    ->ensure();

// a category is an article (its start article) with an additional row here, sharing the id. The id changes when
// another article becomes the start article, so the references to it cascade on update.
Table::get('rex_category')
    ->ensureForeignIdColumn('id', 'rex_article', onDelete: ForeignKey::CASCADE)
    ->ensureColumn(Column::int('priority', unsigned: true))
    ->ensureGlobalColumns()
    ->setPrimaryKey('id')
    ->ensure();

Table::get('rex_category_translation')
    ->ensureForeignIdColumn('category_id', 'rex_category', onUpdate: ForeignKey::CASCADE, onDelete: ForeignKey::CASCADE)
    ->ensureForeignIdColumn('language_id', 'rex_language', onDelete: ForeignKey::CASCADE)
    ->ensureColumn(Column::varchar('name', 255))
    ->setPrimaryKey(['category_id', 'language_id'])
    ->ensure();

// a parent is always a category, and only empty categories can be removed
Table::get('rex_article')
    ->ensureForeignKeyTo('rex_category', ['parent_id' => 'id'], onUpdate: ForeignKey::CASCADE)
    ->alter();

Table::get('rex_article_slice')
    ->ensurePrimaryIdColumn()
    ->ensureColumn(Column::int('article_id', unsigned: true))
    ->ensureColumn(Column::int('language_id', unsigned: true))
    ->ensureColumn(Column::smallint('ctype_id', unsigned: true))
    ->ensureColumn(Column::varchar('module', 191))
    ->ensureColumn(Column::tinyint('revision', unsigned: true))
    ->ensureColumn(Column::smallint('priority', unsigned: true))
    ->ensureColumn(Column::bool('status', default: true))
    ->ensureColumn(Column::json('data', nullable: true))
    ->ensureGlobalColumns()
    // all slice queries filter by article, language and revision and sort within a content section by priority;
    // the leading columns double as the index for the foreign key below
    ->ensureIndex(new Index('find_slices', ['article_id', 'language_id', 'revision', 'ctype_id', 'priority']))
    ->ensureForeignKeyTo('rex_article_translation', ['article_id' => 'article_id', 'language_id' => 'language_id'], onDelete: ForeignKey::CASCADE)
    ->ensure();

// which media and articles the slices reference, written from the module fields on every save of a slice
Table::get('rex_article_slice_reference')
    ->ensureForeignIdColumn('slice_id', 'rex_article_slice', onDelete: ForeignKey::CASCADE)
    ->ensureColumn(Column::varchar('type', 50))
    ->ensureColumn(Column::varchar('target', 255))
    ->setPrimaryKey(['slice_id', 'type', 'target'])
    ->ensureIndex(new Index('target', ['type', 'target']))
    ->ensure();

Table::get('rex_article_slice_history')
    ->ensurePrimaryIdColumn()
    ->ensureColumn(Column::int('slice_id', unsigned: true))
    ->ensureColumn(Column::varchar('history_type', 50))
    ->ensureColumn(Column::datetime('history_date'))
    ->ensureColumn(Column::varchar('history_user', 255))
    ->ensureColumn(Column::int('language_id', unsigned: true))
    ->ensureColumn(Column::smallint('ctype_id', unsigned: true))
    ->ensureColumn(Column::smallint('priority', unsigned: true))
    ->ensureColumn(Column::bool('status', default: true))
    ->ensureColumn(Column::json('data', nullable: true))
    ->ensureColumn(Column::int('article_id', unsigned: true))
    ->ensureColumn(Column::varchar('module', 191))
    ->ensureGlobalColumns()
    ->ensureColumn(Column::tinyint('revision', unsigned: true))
    ->ensureIndex(new Index('snapshot', ['article_id', 'language_id', 'revision', 'history_date']))
    // the cleanup cronjob deletes the old tail of this ever-growing table by date alone
    ->ensureIndex(new Index('history_date', ['history_date']))
    ->ensureForeignKeyTo('rex_article_translation', ['article_id' => 'article_id', 'language_id' => 'language_id'], onDelete: ForeignKey::CASCADE)
    ->ensure();

// The cronjobs themselves are defined in code via #[AsCronjob], the table only holds their runtime state
Table::get('rex_cronjob')
    ->ensureColumn(Column::varchar('name', 191))
    ->ensureColumn(Column::datetime('last_run', nullable: true))
    ->ensureColumn(Column::bool('last_success', nullable: true))
    ->ensureColumn(Column::datetime('execution_start', nullable: true))
    ->setPrimaryKey('name')
    ->ensure();
CronjobManager::removeOrphanedStates();

Table::get('rex_media_category')
    ->ensurePrimaryIdColumn()
    ->ensureColumn(Column::varchar('name', 255))
    ->ensureForeignIdColumn('parent_id', 'rex_media_category', nullable: true)
    ->ensureColumn(Column::varchar('path', 255))
    ->ensureGlobalColumns()
    ->ensure();

Table::get('rex_media')
    ->ensurePrimaryIdColumn()
    ->ensureForeignIdColumn('category_id', 'rex_media_category', nullable: true)
    ->ensureColumn(Column::varchar('filetype', 255, nullable: true))
    ->ensureColumn(Column::varchar('filename', 255))
    ->ensureColumn(Column::varchar('originalname', 255))
    ->ensureColumn(Column::bigint('filesize', unsigned: true))
    ->ensureColumn(Column::mediumint('width', unsigned: true, nullable: true))
    ->ensureColumn(Column::mediumint('height', unsigned: true, nullable: true))
    ->ensureGlobalColumns()
    ->ensureIndex(new Index('filename', ['filename'], Index::UNIQUE))
    ->ensure();

Table::get('rex_media_translation')
    ->ensureForeignIdColumn('media_id', 'rex_media', onDelete: ForeignKey::CASCADE)
    ->ensureForeignIdColumn('language_id', 'rex_language', onDelete: ForeignKey::CASCADE)
    ->ensureColumn(Column::varchar('title', 255))
    ->setPrimaryKey(['media_id', 'language_id'])
    ->ensure();

Table::get('rex_user')
    ->ensurePrimaryIdColumn()
    ->ensureColumn(Column::varchar('name', 255, nullable: true))
    ->ensureColumn(Column::text('description', nullable: true))
    ->ensureColumn(Column::varchar('login', 50))
    ->ensureColumn(Column::varchar('password', 255, nullable: true))
    ->ensureColumn(Column::varchar('email', 255, nullable: true))
    ->ensureColumn(Column::bool('status'))
    ->ensureColumn(Column::bool('admin', default: false))
    ->ensureColumn(Column::varchar('language', 255, nullable: true))
    ->ensureColumn(Column::varchar('startpage', 255, nullable: true))
    ->ensureColumn(Column::varchar('theme', 255, nullable: true))
    ->ensureColumn(Column::smallint('login_tries', unsigned: true, default: 0))
    ->ensureGlobalColumns()
    ->ensureColumn(Column::datetime('password_changed', nullable: true))
    ->ensureColumn(Column::text('previous_passwords', nullable: true))
    ->ensureColumn(Column::bool('password_change_required', default: false))
    ->ensureColumn(Column::datetime('lasttrydate', nullable: true))
    ->ensureColumn(Column::datetime('lastlogin', nullable: true))
    ->ensureIndex(new Index('login', ['login'], Index::UNIQUE))
    ->ensure();

Table::get('rex_user_passkey')
    ->ensureColumn(Column::varchar('id', 255))
    ->ensureForeignIdColumn('user_id', 'rex_user', onUpdate: ForeignKey::CASCADE, onDelete: ForeignKey::CASCADE)
    ->ensureColumn(Column::text('public_key'))
    ->ensureColumn(Column::datetime('createdate'))
    ->setPrimaryKey('id')
    ->ensure();

Table::get('rex_user_role')
    ->ensurePrimaryIdColumn()
    ->ensureColumn(Column::varchar('name', 255, nullable: true))
    ->ensureColumn(Column::text('description', nullable: true))
    ->ensureColumn(Column::text('perms'))
    ->ensureGlobalColumns()
    ->ensure();

Table::get('rex_user_role_assignment')
    ->ensureForeignIdColumn('user_id', 'rex_user', onDelete: ForeignKey::CASCADE)
    ->ensureForeignIdColumn('role_id', 'rex_user_role', onDelete: ForeignKey::CASCADE)
    ->setPrimaryKey(['user_id', 'role_id'])
    ->ensure();

Table::get('rex_user_session')
    ->ensureColumn(Column::varchar('session_id', 255))
    ->ensureForeignIdColumn('user_id', 'rex_user', onUpdate: ForeignKey::CASCADE, onDelete: ForeignKey::CASCADE)
    ->ensureColumn(Column::varchar('cookie_key', 88, nullable: true))
    ->ensureColumn(Column::varchar('passkey_id', 255, nullable: true))
    ->ensureColumn(Column::varchar('ip', 39)) // max for ipv6
    ->ensureColumn(Column::varchar('useragent', 255))
    ->ensureColumn(Column::datetime('starttime'))
    ->ensureColumn(Column::datetime('last_activity'))
    ->setPrimaryKey('session_id')
    ->ensureIndex(new Index('cookie_key', ['cookie_key'], Index::UNIQUE))
    ->ensureForeignKeyTo('rex_user_passkey', ['passkey_id' => 'id'], ForeignKey::CASCADE, ForeignKey::CASCADE)
    ->ensure();

$defaultConfig = [
    'start_article_id' => 1,
    'notfound_article_id' => 1,
    'article_history' => false,
    'article_work_version' => false,
];

Config::refresh();
foreach ($defaultConfig as $key => $value) {
    if (!Core::hasConfig($key)) {
        Core::setConfig($key, $value);
    }
}
