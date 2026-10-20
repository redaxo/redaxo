<?php

namespace Redaxo\Core\Util\Markdown;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\RawMarkupContainerInterface;
use League\CommonMark\Node\StringContainerHelper;
use Redaxo\Core\Util\Str;
use Redaxo\Core\Util\Type;

/**
 * Assigns unique ids to all headings and collects the ones within the given levels for a table-of-content.
 *
 * @internal
 */
final class HeadingIdListener
{
    /** @var list<array{level: int, id: string, text: string}> */
    public private(set) array $headings = [];

    /** @var array<string, true> */
    private array $ids = [];

    public function __construct(
        private readonly int $topLevel,
        private readonly int $bottomLevel,
    ) {}

    public function __invoke(DocumentParsedEvent $event): void
    {
        foreach ($event->getDocument()->iterator() as $node) {
            if (!$node instanceof Heading) {
                continue;
            }

            $text = StringContainerHelper::getChildText($node, [RawMarkupContainerInterface::class]);

            $id = Type::nullOrString($node->data->get('attributes/id', null));
            if (null === $id) {
                $baseId = $id = Str::normalize($text, '-');

                for ($i = 1; isset($this->ids[$id]); ++$i) {
                    $id = $baseId . '-' . $i;
                }

                $node->data->set('attributes/id', $id);
            }

            $this->ids[$id] = true;

            $level = $node->getLevel();
            if ($level >= $this->topLevel && $level <= $this->bottomLevel) {
                $this->headings[] = [
                    'level' => $level,
                    'id' => $id,
                    'text' => $text,
                ];
            }
        }
    }
}
