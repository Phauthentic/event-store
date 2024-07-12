<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Tests;

use Phauthentic\EventStore\Event;
use Phauthentic\EventStore\InMemoryEventStore;

class InMemoryEventStoreTest extends AbstractEventStoreTestCase
{
    public function testReplyFromPositionZero(): void
    {
        $eventStore = new InMemoryEventStore();

        $domainEvents = $this->getEvents();

        $eventStore->storeEvent($domainEvents[0]);
        $eventStore->storeEvent($domainEvents[1]);

        $events = [];
        foreach ($eventStore->replyFromPosition($domainEvents[0]->getAggregateId(), 0) as $event) {
            $events[] = $event;
        }

        $this->assertCount(2, $events);
    }

    public function testReplyFromPositionGreaterThanZero(): void
    {
        $eventStore = new InMemoryEventStore();

        $domainEvents = $this->getEvents(4);

        $eventStore->storeEvent($domainEvents[0]);
        $eventStore->storeEvent($domainEvents[1]);
        $eventStore->storeEvent($domainEvents[2]);
        $eventStore->storeEvent($domainEvents[3]);

        $events = [];
        foreach ($eventStore->replyFromPosition($domainEvents[0]->getAggregateId(), 2) as $event) {
            $events[] = $event;
        }

        $this->assertCount(2, $events);
    }

    public function testReplyFromPositionWithAHigherPositionThanExisting(): void
    {
        $eventStore = new InMemoryEventStore();

        $domainEvents = $this->getEvents(4);

        $eventStore->storeEvent($domainEvents[0]);

        $events = [];
        foreach ($eventStore->replyFromPosition($domainEvents[0]->getAggregateId(), 1000) as $event) {
            $events[] = $event;
        }

        $this->assertCount(0, $events);
    }
}
