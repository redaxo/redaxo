<?php

namespace Redaxo\Core\Cronjob\Type;

use DateTimeImmutable;
use Redaxo\Core\Content\ArticleSliceHistory;
use Redaxo\Core\Translation\I18n;

class ClearArticleHistoryType extends AbstractType
{
    public function execute()
    {
        $period = $this->getParam('period');

        if ('' == $period) {
            $this->setMessage('Article-History Cleanup failed: `' . $period . '` is not a period');
            return false;
        }

        $deleteDate = new DateTimeImmutable('- ' . $period);

        ArticleSliceHistory::clearHistoryByDate($deleteDate);
        $this->setMessage('Article-History Cleanup done with `' . $period . '` as period');

        return true;
    }

    public function getTypeName()
    {
        return I18n::msg('structure_history_cleanup');
    }

    public function getParamFields()
    {
        $fields = [];

        $fields[] = [
            'label' => I18n::msg('structure_history_cleanup_after'),
            'name' => 'period',
            'type' => 'select',
            'options' => [
                '7 days' => I18n::msg('structure_history_days', 7),
                '14 days' => I18n::msg('structure_history_days', 14),
                '1 month' => I18n::msg('structure_history_months', 1),
                '6 months' => I18n::msg('structure_history_months', 6),
                '1 year' => I18n::msg('structure_history_years', 1),
            ],
        ];

        return $fields;
    }
}
