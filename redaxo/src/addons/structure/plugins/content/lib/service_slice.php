<?php

/**
 * @package redaxo\structure\content
 */
class rex_slice_service
{
    /**
     * @param array $data
     *
     * @throws \rex_api_exception
     * @throws \rex_sql_exception
     *
     * @return string
     */
    public static function addSlice($data): string
    {
        if (!is_array($data)) {
            throw new rex_api_exception('Expecting $data to be an array!');
        }
        self::reqKey($data, 'clang_id');
        self::reqKey($data, 'ctype_id');
        self::reqKey($data, 'article_id');
        self::reqKey($data, 'module_id');
        if (rex_plugin::get('structure', 'version')->isAvailable()) {
            self::reqKey($data, 'revision');
        }

        if ($data['priority'] <= 0) {
            $data['priority'] = 1;
        }

        $article_revision = 0;
        $slice_revision = 0;

        $OOArt = rex_article::get($data['article_id'], $data['clang_id']);
        $category_id = $OOArt->getCategoryId();

        $message = rex_i18n::msg('slice_added');

        $ASLICE = rex_sql::factory();
        $user = self::getUser();

        $ASLICE->setTable(rex::getTablePrefix() . 'article_slice');

        foreach ($data as $key => $value) {
            $ASLICE->setValue($key, $value);
        }

        $ASLICE->addGlobalCreateFields($user);
        $ASLICE->addGlobalUpdateFields($user);

        try {
            $ASLICE->insert();
        } catch (rex_sql_exception $e) {
            throw new rex_api_exception($e);
        }

        $slice_id = $ASLICE->getLastId();

        rex_sql_util::organizePriorities(
            rex::getTable('article_slice'),
            'priority',
            'article_id=' . (int) $data['article_id'] . ' AND clang_id=' . (int) $data['clang_id'] . ' AND ctype_id=' . (int) $data['ctype_id'] . ' AND revision=' . (int) $slice_revision,
            'priority, updatedate DESC'
        );

        rex_article_cache::delete($data['article_id'], $data['clang_id']);

        $function = '';
        $epParams = [
            'article_id' => $data['article_id'],
            'clang' => $data['clang_id'],
            'function' => $function,
            'slice_id' => $slice_id,
            'page' => rex_be_controller::getCurrentPage(),
            'ctype' => $data['ctype_id'],
            'category_id' => $category_id,
            'module_id' => $data['module_id'],
            'article_revision' => &$article_revision,
            'slice_revision' => &$slice_revision,
        ];

        // ----- EXTENSION POINT
        $message = rex_extension::registerPoint(new rex_extension_point('SLICE_ADDED', $message, $epParams));

        return $message;
    }

    /**
     * Checks whether the required array key $keyName isset.
     *
     * @param array  $array   The array
     * @param string $keyName The key
     *
     * @throws rex_api_exception
     */
    protected static function reqKey($array, $keyName): void
    {
        if (!isset($array[$keyName])) {
            throw new rex_api_exception('Missing required parameter "' . $keyName . '"!');
        }
    }

    private static function getUser()
    {
        if (rex::getUser()) {
            return rex::getUser()->getLogin();
        }

        return rex::getEnvironment();
    }
}
