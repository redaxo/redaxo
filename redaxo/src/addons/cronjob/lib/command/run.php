<?php

use Symfony\Component\Console\Exception\InvalidArgumentException as SymfonyInvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @package redaxo\cronjob
 *
 * @internal
 */
class rex_command_cronjob_run extends rex_console_command
{
    protected function configure()
    {
        $this
            ->setDescription('Executes cronjobs of the "script" environment')
            ->addOption('job', null, InputOption::VALUE_OPTIONAL, 'Execute single job (selected interactively or given by id)', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        // indicator constant, kept for BC
        define('REX_CRONJOB_SCRIPT', true);

        $job = $input->getOption('job');

        if (false !== $job) {
            return $this->executeSingleJob($io, $job);
        }

        $nexttime = rex_package::get('cronjob')->getConfig('nexttime', 0);

        if ($nexttime != 0 && time() >= $nexttime) {
            rex_cronjob_manager_sql::factory()->check();

            $io->success('Cronjobs checked.');
            return 0;
        }

        $io->success('Cronjobs skipped.');
        return 0;
    }

    private function executeSingleJob(SymfonyStyle $io, $id)
    {
        $manager = rex_cronjob_manager_sql::factory();

        if (null === $id) {
            $jobs = rex_sql::factory()->getArray('
                SELECT id, name
                FROM ' . REX_CRONJOB_TABLE . '
                WHERE environment LIKE "%|script|%"
                ORDER BY id
            ');
            $jobs = array_column($jobs, 'name', 'id');

            $question = new ChoiceQuestion('Which cronjob should be executed?', $jobs);
            $question->setValidator(function ($selected) use ($jobs) {
                $selected = trim($selected);

                if (!isset($jobs[$selected])) {
                    throw new SymfonyInvalidArgumentException(sprintf('Value "%s" is invalid.', $selected));
                }

                return $selected;
            });

            $id = $io->askQuestion($question);
            $name = $jobs[$id];
        } else {
            $name = $manager->getName($id);
        }

        $success = $manager->tryExecute($id);

        $msg = '';
        if ($manager->hasMessage()) {
            $msg = ': '.$manager->getMessage();
        }

        if ($success) {
            $io->success(sprintf('Cronjob "%s" executed successfully%s.', $name, $msg));
        } else {
            $io->error(sprintf('Cronjob "%s" failed%s.', $name, $msg));
        }
    }
}
