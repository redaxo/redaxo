<?php

/**
 * @package redaxo\structure\history
 */
class rex_cronjob_structure_history extends rex_cronjob
{
    public function execute()
    {
        $period = $this->getParam('period');

        if ('' == $period) {
            $this->setMessage('Article-History Cleanup failed: `' . $period . '` is not a period');
            return false;
        }

        $deleteDate = new DateTimeImmutable('- ' . $period);

        rex_article_slice_history::clearHistoryByDate($deleteDate);
        $this->setMessage('Article-History Cleanup done with `' . $period . '` as period');

        return true;
    }

    public function getTypeName()
    {
        return rex_i18n::msg('structure_history_cleanup');
    }

    public function getParamFields()
    {
        $fields = [];

        $fields[] = [
            'label' => rex_i18n::msg('structure_history_cleanup_after'),
            'name' => 'period',
            'type' => 'select',
            'options' => [
                '7 days' => rex_i18n::msg('structure_history_days', 7),
                '14 days' => rex_i18n::msg('structure_history_days', 14),
                '1 month' => rex_i18n::msg('structure_history_months', 1),
                '6 months' => rex_i18n::msg('structure_history_months', 6),
                '1 year' => rex_i18n::msg('structure_history_years', 1),
            ],
        ];

        return $fields;
    }
}
