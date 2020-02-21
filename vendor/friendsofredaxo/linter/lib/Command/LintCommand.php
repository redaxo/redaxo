<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

final class LintCommand extends Command
{
    public const ERR_YAML = 1;
    public const ERR_PHP = 2;
    public const ERR_JSON = 4;
    public const ERR_CSS = 8;
    public const ERR_SQL = 16;

    protected function configure()
    {
        $this->setName('rexlint')
            ->addArgument('dir', InputArgument::OPTIONAL, 'The directory', '.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rootPath = dirname(__FILE__, 3);

        // If package vendor folder isn't available use project vendor folder
        // In Dev Env the vendor-binaries folder is located in the project root
        // If this package is loaded via composer the vendor-binaries folder is located in project root and not in linter root
        if (!is_dir($rootPath.'/vendor')) {
            $rootPath = dirname($rootPath, 3);
        }

        // some lecture on "find -exec vs. find | xargs"
        // https://www.everythingcli.org/find-exec-vs-find-xargs/

        $style = new SymfonyStyle($input, $output);
        $dir = rtrim($input->getArgument('dir'), DIRECTORY_SEPARATOR);

        $processes[] = [
            self::ERR_PHP,
            'PHP checks',
            $this->asyncProc([$rootPath.'/vendor/bin/parallel-lint', '--exclude', $dir.'/vendor', '--exclude', $dir.'/.idea', $dir]),
        ];
        $processes[] = [
            self::ERR_JSON,
            'JSON checks',
            $this->asyncProc(['find', $dir, '-type', 'f', '-name', '*.json', '!', '-path', '*/vendor/*', '-exec', $rootPath.'/vendor/bin/jsonlint', '{}', '+']),
        ];
        $processes[] = [
            self::ERR_SQL,
            'SQL checks',
            $this->asyncProc(['find', $dir, '-name', '*.sql', '!', '-path', '*/vendor/*', '-exec',  __DIR__.'/../../bin/lint-file.sh', '{}', '+']),
        ];

        // $this->syncProc(['npm', 'install', 'csslint']);

        // we only want to find errors, no style checks
        $csRules = 'order-alphabetical,important,ids,font-sizes,floats';
        //$processes[] = [
        //    self::ERR_CSS,
        //    'CSS checks',
        //    $this->asyncProc(['find', $dir, '-name', '*.css', '!', '-path', '*/vendor/*', '-exec', 'node_modules/.bin/csslint', '--ignore='.$csRules, '{}', '+']),
        //];

        $exit = 0;
        foreach ($processes as $struct) {
            [
                $exitCode,
                $label,
                $process
            ] = $struct;

            $process->wait();

            $succeed = $process->isSuccessful();
            if ($exitCode == self::ERR_PHP) {
                if (strpos($process->getOutput(), 'No file found to check') !== false) {
                    $succeed = true;
                }
            }

            if (!$succeed) {
                $style->section($label);
                echo $process->getOutput();
                echo $process->getErrorOutput();
                $style->error("$label failed\n");
                $exit = $exit | $exitCode;
            } else {
                if ($output->isVerbose()) {
                    echo $process->getCommandLine()."\n";
                    echo $process->getOutput();
                    echo $process->getErrorOutput();
                }
                $style->success("$label successfull\n");
            }
        }

        // yaml-lint only supports one file at a time
        $label = 'YAML checks';
        $succeed = $this->syncFindExec(['find', $dir, '-type', 'f', '-name', '*.yml', '!', '-path', '*/vendor/*'], [$rootPath.'/vendor/bin/yaml-lint']);

        if (!$succeed) {
            $style->section('YAML checks');
            $style->error("$label failed\n");
            $exit = $exit | self::ERR_YAML;
        } else {
            $style->success("$label successfull\n");
        }

        return $exit;
    }

    private function syncFindExec(array $findCmd, array $execCmd)
    {
        $processes = [];

        $process = new Process($findCmd);
        $process->mustRun(static function ($type, $buffer) use (&$processes, $execCmd) {
            if (Process::ERR === $type) {
                throw new Exception($buffer);
            }
            foreach (explode("\n", trim($buffer)) as $ymlFile) {
                $cmd = $execCmd;
                $cmd[] = $ymlFile;

                $process = new Process($cmd);
                $process->start();
                $processes[] = $process;
            }
        });

        foreach ($processes as $subProcess) {
            $subProcess->wait();

            if (!$subProcess->isSuccessful()) {
                echo $subProcess->getCommandLine()."\n";
                echo $subProcess->getOutput();
                echo $subProcess->getErrorOutput();

                return false;
            }
        }
        return true;
    }

    private function asyncProc(array $cmd): Process
    {
        $process = new Process($cmd);
        $process->start();
        return $process;
    }

    private function syncProc(array $cmd)
    {
        $syncP = new Process($cmd);
        $syncP->mustRun();
        echo $syncP->getOutput();
    }
}
