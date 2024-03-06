<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Tests;

use Phauthentic\EventStore\Event;
use Phauthentic\EventStore\InMemoryEventStore;

class InMemoryEventStoreTest extends AbstractEventStoreTestCase
{
    public function testStoreEvent()
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
}
