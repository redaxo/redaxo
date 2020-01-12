<?php

/**
 * REX_CATEGORY[xzy]
 * REX_CATEGORY[field=xzy]
 * REX_CATEGORY[field=xzy id=3]
 * REX_CATEGORY[field=xzy id=3 clang=2].
 *
 * Attribute:
 *   - field    => Feld der Kategorie, das ausgegeben werden soll
 *   - clang    => ClangId der Kategorie
 *
 * @package redaxo\structure
 */
class rex_var_category extends rex_var
{
    /**
     * Werte für die Ausgabe.
     */
    protected function getOutput()
    {
        $field = $this->getParsedArg('field');
        if (!$field) {
            return false;
        }

        $category_id = $this->getParsedArg('id', '$this->getValue(\'category_id\')');
        $clang = $this->getParsedArg('clang', 'null');

        return self::class . '::getCategoryValue(' . $category_id . ', ' . $field . ', ' . $clang . ')';
    }

    public static function getCategoryValue($id, $field, $clang = null)
    {
        if (null === $clang) {
            $clang = rex_clang::getCurrentId();
        }
        $cat = rex_category::get($id, $clang);
        if ($cat) {
            return rex_escape($cat->getValue($field));
        }
    }
}
