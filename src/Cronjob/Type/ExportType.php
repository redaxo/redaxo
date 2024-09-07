<?php

namespace Redaxo\Core\Cronjob\Type;

use DateTimeImmutable;
use Redaxo\Core\Backup\Backup;
use Redaxo\Core\Backup\FileCompressor;
use Redaxo\Core\Core;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Mailer\Mailer;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\Str;

use const GLOB_NOSORT;
use const SORT_NUMERIC;

class ExportType extends AbstractType
{
    public const DEFAULT_FILENAME = '%REX_SERVER_%Y%m%d_%H%M_rex%REX_VERSION';

    public function execute()
    {
        $filename = $this->getParam('filename', self::DEFAULT_FILENAME);
        $filename = str_replace('%REX_SERVER', Str::normalize(Core::getServerName(), '-'), $filename);
        $filename = str_replace('%REX_VERSION', Core::getVersion(), $filename);
        $now = new DateTimeImmutable();
        $filename = str_replace(
            ['%Y', '%m', '%d', '%H', '%M', '%S'],
            [$now->format('Y'), $now->format('m'), $now->format('d'), $now->format('H'), $now->format('i'), $now->format('s')],
            $filename,
        );
        $file = $filename;
        $dir = Backup::getDir() . '/';
        $ext = '.cronjob.sql';
        $filename .= $ext;

        $excludedTables = $this->getParam('exclude_tables');
        $excludedTables = $excludedTables ? explode('|', $excludedTables) : [];
        $tables = array_diff(Backup::getTables(), $excludedTables);

        if (is_file($dir . $file . $ext)) {
            $i = 1;
            while (is_file($dir . $file . '_' . $i . $ext)) {
                ++$i;
            }
            $file = $file . '_' . $i;
        }
        $exportFilePath = $dir . $file . $ext;

        if (Backup::exportDb($exportFilePath, $tables)) {
            $message = Path::basename($exportFilePath) . ' created';

            if ($this->getParam('compress')) {
                $compressor = new FileCompressor();
                $gzPath = $compressor->gzCompress($exportFilePath);
                if ($gzPath) {
                    File::delete($exportFilePath);

                    $message = Path::basename($gzPath) . ' created';
                    $exportFilePath = $gzPath;
                    $filename .= '.gz';
                }
            }

            if ($this->getParam('delete_interval')) {
                $allSqlfiles = array_merge(
                    glob(Path::coreData('backup/*' . $ext), GLOB_NOSORT),
                    glob(Path::coreData('backup/*' . $ext . '.gz'), GLOB_NOSORT),
                );
                $backups = [];
                $limit = strtotime('-1 month'); // Generelle Vorhaltezeit: 1 Monat

                foreach ($allSqlfiles as $sqlFile) {
                    $timestamp = filectime($sqlFile);

                    if ($timestamp > $limit) {
                        // wenn es die generelle Vorhaltezeit unterschreitet
                        continue;
                    }

                    $backups[$sqlFile] = $timestamp;
                }

                asort($backups, SORT_NUMERIC);

                $step = '';
                $countDeleted = 0;

                foreach ($backups as $backup => $timestamp) {
                    $stepLast = $step;
                    $step = date($this->getParam('delete_interval'), (int) $timestamp);

                    if ($stepLast !== $step) {
                        // wenn es zu diesem Interval schon ein Backup gibt
                        continue;
                    }

                    // dann löschen
                    File::delete($backup);
                    ++$countDeleted;
                }

                if ($countDeleted) {
                    $message .= ', ' . $countDeleted . ' old backup(s) deleted';
                }
            }

            if ($this->getParam('sendmail')) {
                $mail = new Mailer();
                $mail->addAddress($this->getParam('mailaddress'));
                $mail->Subject = I18n::rawMsg('backup_mail_subject');
                $mail->Body = I18n::rawMsg('backup_mail_body', Core::getServerName());
                $mail->addAttachment($exportFilePath, $filename);
                if ($mail->send()) {
                    $this->setMessage($message . ', mail sent');

                    return true;
                }
                $this->setMessage($message . ', mail not sent');

                return false;
            }

            $this->setMessage($message);

            return true;
        }
        $this->setMessage($file . $ext . ' not created');

        return false;
    }

    public function getTypeName()
    {
        return I18n::msg('backup_database_export');
    }

    public function getParamFields()
    {
        $tables = Backup::getTables();

        $fields = [
            [
                'label' => I18n::msg('backup_filename'),
                'name' => 'filename',
                'type' => 'text',
                'default' => self::DEFAULT_FILENAME,
                'notice' => I18n::msg('backup_filename_notice'),
            ],
            [
                'label' => I18n::msg('backup_exclude_tables'),
                'name' => 'exclude_tables',
                'type' => 'select',
                'attributes' => ['multiple' => 'multiple', 'data-live-search' => 'true'],
                'options' => array_combine($tables, $tables),
                'notice' => I18n::msg('backup_exclude_tables_notice'),
            ],
            [
                'name' => 'sendmail',
                'type' => 'checkbox',
                'options' => [1 => I18n::msg('backup_send_mail')],
            ],
        ];

        $fields[] = [
            'label' => I18n::msg('backup_mailaddress'),
            'name' => 'mailaddress',
            'type' => 'text',
            'visible_if' => ['sendmail' => 1],
        ];

        $fields[] = [
            'name' => 'compress',
            'type' => 'checkbox',
            'options' => [1 => I18n::msg('backup_compress')],
        ];

        $fields[] = [
            'label' => I18n::msg('backup_delete_interval'),
            'name' => 'delete_interval',
            'type' => 'select',
            'options' => [
                '0' => I18n::msg('backup_delete_interval_off'),
                'YW' => I18n::msg('backup_delete_interval_weekly'),
                'YM' => I18n::msg('backup_delete_interval_monthly'), ],
            'default' => 'YW',
            'notice' => I18n::msg('backup_delete_interval_notice'),
        ];

        return $fields;
    }
}
