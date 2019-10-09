<?php

/**
 * @package redaxo\structure
 */
class rex_slice_service
{

    /**
     * @param array $data
     *
     * @throws \rex_sql_exception
     * @throws \rex_api_exception
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
        self::reqKey($data, 'priority');
        if (rex_plugin::get('bloecks', 'status')->isAvailable()) {
            self::reqKey($data, 'status');
        }
        if (rex_plugin::get('structure', 'version')->isAvailable()) {
            self::reqKey($data, 'revision');
        }

        if ($data['priority'] <= 0) {
            $data['priority'] = 1;
        }

        $message = rex_i18n::msg('slice_added');

        $ASLICE = rex_sql::factory();
        $user = self::getUser();

        $ASLICE->setTable(rex::getTablePrefix() . 'article_slice');
        $slice_id = $ASLICE->setNewId('id');

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

        rex_article_cache::delete($data['article_id'], $data['clang_id']);

        // ----- EXTENSION POINT
        $message = rex_extension::registerPoint(new rex_extension_point('SLICE_ADDED', $message, [
            'id' => $slice_id,
            'data' => $data,
        ]));

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

        if (method_exists(rex::class, 'getEnvironment')) {
            return rex::getEnvironment();
        }

        return 'frontend';
    }
}
