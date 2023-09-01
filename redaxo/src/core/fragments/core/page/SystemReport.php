<?php

use Redaxo\Core\Fragment\Component\Card;
use Redaxo\Core\Fragment\Component\CopyButton;
use Redaxo\Core\Fragment\Component\Details;
use Redaxo\Core\Fragment\Component\Icon;
use Redaxo\Core\Fragment\Component\IconLibrary;
use Redaxo\Core\Fragment\Html;
use Redaxo\Core\Fragment\Page\SystemReport;

/**
 * @var SystemReport $this
 * @psalm-scope-this SystemReport
 */
?>

<?php foreach ($this->report->get() as $title => $group): ?>
    <?= (new Card(
        header: $title,
        body: new Html(static function () use ($title, $group) { ?>
            <table class="table">
            <?php foreach ($group as $label => $value): ?>
                <tr>
                    <th width="120"><?= rex_escape($label) ?></th>
                    <td>
                        <?php
                        if (rex_system_report::TITLE_PACKAGES === $title || rex_system_report::TITLE_REDAXO === $title) {
                            if (null === $value) {
                                throw new rex_exception('Package ' . $label . ' does not define a proper version in its package.yml');
                            }
                            if (rex_version::isUnstable($value)) {
                                echo (new Icon(IconLibrary::VersionUnstable))->render() . ' ' . rex_escape($value);
                            }
                        } elseif (is_bool($value)) {
                            echo $value ? 'yes' : 'no';
                        } else {
                            echo rex_escape($value);
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </table>
        <?php }),
    ))->render() ?>
<?php endforeach ?>
<?= (new Details(
    summary: 'Markdown',
    body: new Html(function () { ?>
        <?= (new CopyButton(
            from: 'rex-system-report-markdown',
        ))->render() ?>
        <pre><code id="rex-system-report-markdown"><?= rex_escape($this->report->asMarkdown()) ?></code></pre>
    <?php }),
))->render() ?>
