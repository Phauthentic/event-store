<?php

declare(strict_types=1);

namespace Phauthentic\EventStore;

use EmptyIterator;
use Iterator;
use Phauthentic\EventStore\Exception\EventStoreException;

/**
 *
 */
class InMemoryEventStore implements EventStoreInterface
{
    /**
     * @var array<string, array<int, EventInterface>>
     */
    protected array $aggregates = [];

    public function storeEvent(EventInterface $event): void
    {
        $aggregateId = $event->getAggregateId();
        $version = $event->getAggregateVersion();

        if ($version < 1) {
            throw new EventStoreException(sprintf(
                'Event version must be positive, got %d',
                $version
            ));
        }

        if (isset($this->aggregates[$aggregateId][$version])) {
            throw new EventStoreException(sprintf(
                'Duplicate event version %d for aggregate %s',
                $version,
                $aggregateId
            ));
        }

        $this->aggregates[$aggregateId][$version] = $event;
    }

    public function replyFromPosition(ReplyFromPositionQuery $replyFromPositionQuery): Iterator
    {
        $aggregateId = $replyFromPositionQuery->aggregateId;
        $position = $replyFromPositionQuery->position;

        if (
            !isset($this->aggregates[$aggregateId])
            || empty($this->aggregates[$aggregateId])
        ) {
            return new EmptyIterator();
        }

        $events = $this->aggregates[$aggregateId];
        ksort($events);

        foreach ($events as $storePosition => $event) {
            if ($storePosition >= $position) {
                yield $event;
            }
        }
    }
}
