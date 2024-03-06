<?php

declare(strict_types=1);

namespace Phauthentic\EventStore;

use EmptyIterator;
use Iterator;

class InMemoryEventStore implements EventStoreInterface
{
    /**
     * @var array<string, array<int, EventInterface>>
     */
    protected array $aggregates = [];

    public function storeEvent(EventInterface $event): void
    {
        $eventCount = 0;
        if (isset($this->aggregates[$event->getAggregateId()])) {
            $eventCount = count($this->aggregates[$event->getAggregateId()]);
        }

        $this->aggregates[$event->getAggregateId()][$eventCount + 1] = $event;
    }

    public function replyFromPosition(string $aggregateId, int $position = 0): Iterator
    {
        if (!isset($this->aggregates[$aggregateId]) || empty($this->aggregates[$aggregateId])) {
            return new EmptyIterator();
        }

        foreach ($this->aggregates[$aggregateId] as $event) {
            yield $event;
        }
    }
}
