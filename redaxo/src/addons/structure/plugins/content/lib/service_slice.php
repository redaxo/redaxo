<?php

/**
 * @package redaxo\structure\content
 */
class rex_slice_service
{
    /**
     * @throws rex_api_exception
     */
    public static function addSlice(array $data): string
    {
        if (!is_array($data)) {
            throw new rex_api_exception('Expecting $data to be an array!');
        }

        self::reqKey($data, 'clang_id');
        self::reqKey($data, 'ctype_id');
        self::reqKey($data, 'article_id');
        self::reqKey($data, 'module_id');

        $data['revision'] = $data['revision'] ?? 0;

        $where = 'article_id=' . (int) $data['article_id'] . ' AND clang_id=' . (int) $data['clang_id'] . ' AND ctype_id=' . (int) $data['ctype_id'] . ' AND revision=' . (int) $data['revision'];

        if (!isset($data['priority'])) {
            $prevSlice = rex_sql::factory();
            $prevSlice->setQuery('SELECT IFNULL(MAX(priority),0)+1 as priority FROM ' . rex::getTable('article_slice') . ' WHERE '.$where);

            $data['priority'] = $prevSlice->getValue('priority');
        } elseif ($data['priority'] <= 0) {
            $data['priority'] = 1;
        }

        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('article_slice'));

        foreach ($data as $key => $value) {
            $sql->setValue($key, $value);
        }

        $sql->addGlobalCreateFields();
        $sql->addGlobalUpdateFields();

        try {
            $sql->insert();
            $sliceId = $sql->getLastId();

            rex_sql_util::organizePriorities(
                rex::getTable('article_slice'),
                'priority',
                $where,
                'priority, updatedate DESC'
            );
        } catch (rex_sql_exception $e) {
            throw new rex_api_exception($e);
        }

        rex_article_cache::delete($data['article_id'], $data['clang_id']);

        $message = rex_i18n::msg('slice_added');

        // ----- EXTENSION POINT
        $message = rex_extension::registerPoint(new rex_extension_point('SLICE_ADDED', $message, [
            'article_id' => $data['article_id'],
            'clang' => $data['clang_id'],
            'function' => '',
            'slice_id' => $sliceId,
            'page' => rex_be_controller::getCurrentPage(),
            'ctype' => $data['ctype_id'],
            'category_id' => rex_article::get($data['article_id'], $data['clang_id'])->getCategoryId(),
            'module_id' => $data['module_id'],
            'article_revision' => 0,
            'slice_revision' => $data['revision'],
        ]));

        return $message;
    }

    /**
     * Checks whether the required array key $keyName isset.
     *
     * @throws rex_api_exception
     */
    private static function reqKey(array $array, string $keyName): void
    {
        if (!isset($array[$keyName])) {
            throw new rex_api_exception('Missing required parameter "' . $keyName . '"!');
        }
    }
}
