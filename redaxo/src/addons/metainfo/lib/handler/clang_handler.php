<?php

/**
 * @package redaxo\metainfo
 *
 * @internal
 */
class rex_metainfo_clang_handler extends rex_metainfo_handler
{
    const PREFIX = 'clang_';
    const CONTAINER = 'rex-clang-metainfo';

    public function renderToggleButton(rex_extension_point $ep)
    {
        $fields = parent::getSqlFields(self::PREFIX);
        if ($fields->getRows() >= 1) {
            $return = '<a class="btn btn-default collapsed" data-toggle="collapse" href="#' . self::CONTAINER . '"><i class="rex-icon rex-icon-structure-category-metainfo"></i></a>';

            return $ep->getSubject() . $return;
        }

        return $ep->getSubject();
    }

    public function handleSave(array $params, rex_sql $sqlFields)
    {
        if (rex_request_method() != 'post' || !isset($params['id'])) {
            return $params;
        }

        $sql = rex_sql::factory();
        // $sql->setDebug();
        $sql->setTable(rex::getTablePrefix() . 'clang');
        $sql->setWhere('id=:id', ['id' => $params['id']]);

        parent::fetchRequestValues($params, $sql, $sqlFields);

        // do the save only when metafields are defined
        if ($sql->hasValues()) {
            $sql->update();
        }

        rex_clang_service::generateCache();

        return $params;
    }

    protected function buildFilterCondition(array $params)
    {
    }

    public function renderFormItem($field, $tag, $tag_attr, $id, $label, $labelIt, $typeLabel)
    {
        $element = $field;

        if ($typeLabel == 'legend') {
            $element = '<h3 class="form-legend">' . $label . '</h3>';
        }

        return $element;
    }

    public function extendForm(rex_extension_point $ep)
    {
        $params = $ep->getParams();
        if (isset($params['sql'])) {
            $params['activeItem'] = $params['sql'];
        }

        $result = '
            <tr id="' . self::CONTAINER . '" class="collapse mark">
                <td colspan="2"></td>
                <td colspan="6">
                    <div class="rex-collapse-content">
                        ' . parent::renderFormAndSave(self::PREFIX, $params) . '
                    </div>
                </td>
            </tr>';

        // Bei CLANG_ADDED und CLANG_UPDATED nur speichern und kein Formular zurückgeben
        if ('CLANG_UPDATED' == $ep->getName() || 'CLANG_ADDED' == $ep->getName()) {
            return $ep->getSubject();
        }
        return $ep->getSubject() . $result;
    }
}

$clangHandler = new rex_metainfo_clang_handler();

rex_extension::register('CLANG_FORM_ADD', [$clangHandler, 'extendForm']);
rex_extension::register('CLANG_FORM_EDIT', [$clangHandler, 'extendForm']);

rex_extension::register('CLANG_ADDED', [$clangHandler, 'extendForm']);
rex_extension::register('CLANG_UPDATED', [$clangHandler, 'extendForm']);

rex_extension::register('CLANG_FORM_BUTTONS', [$clangHandler, 'renderToggleButton']);
