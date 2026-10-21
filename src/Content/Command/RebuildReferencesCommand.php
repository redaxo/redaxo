<?php

namespace Redaxo\Core\Content\Command;

use Redaxo\Core\Console\AbstractCommand;
use Redaxo\Core\Content\ArticleSliceReference;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;

/**
 * @internal
 */
#[AsCommand(name: 'content:rebuild-references', description: 'Rebuilds the media and article references of all slices, e.g. after module fields have changed')]
final class RebuildReferencesCommand extends AbstractCommand
{
    public function __invoke(SymfonyStyle $io): int
    {
        $count = ArticleSliceReference::rebuild();

        $io->success(sprintf('Rebuilt the references of %d slices.', $count));

        return Command::SUCCESS;
    }
}
