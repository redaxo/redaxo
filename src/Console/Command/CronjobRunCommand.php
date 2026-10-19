<?php

namespace Redaxo\Core\Console\Command;

use Redaxo\Core\Cronjob\CronjobManager;
use Redaxo\Core\Cronjob\CronjobRegistry;
use Redaxo\Core\Cronjob\CronjobResult;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;

/**
 * @internal
 */
#[AsCommand(
    name: 'cronjob:run',
    description: 'Executes the due cronjobs, or the given cronjob immediately',
    help: 'Without argument, the command is meant to be called every minute by the system cron. It only produces output if a cronjob has run.',
)]
final class CronjobRunCommand extends AbstractCommand
{
    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Name of a cronjob to execute immediately, regardless of its schedule')] ?string $name = null,
    ): int {
        if (null !== $name) {
            $cronjob = CronjobRegistry::get($name);
            if (null === $cronjob) {
                $io->error(sprintf('Cronjob "%s" not found.', $name));

                return Command::FAILURE;
            }

            $results = [CronjobManager::run($cronjob)];
        } else {
            $results = CronjobManager::runDue();
        }

        $failed = false;
        foreach ($results as $result) {
            $this->printResult($io, $result);
            $failed = $failed || !$result->success;
        }

        return $failed ? Command::FAILURE : Command::SUCCESS;
    }

    private function printResult(SymfonyStyle $io, CronjobResult $result): void
    {
        $message = $result->name . ('' === $result->message ? '' : ': ' . $result->message);

        if ($result->success) {
            $io->success($message);
        } else {
            $io->error($message);
        }
    }
}
