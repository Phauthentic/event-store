<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Tests;

use Phauthentic\EventStore\Event;
use Phauthentic\EventStore\InMemoryEventStore;
use Phauthentic\EventStore\ReplyFromPositionQuery;

class InMemoryEventStoreTest extends AbstractEventStoreTestCase
{
    public function testReplyFromPositionZero(): void
    {
        $eventStore = new InMemoryEventStore();
        $this->storeNumberOfEvents($eventStore, '123', 2);

        $events = [];
        foreach ($eventStore->replyFromPosition(new ReplyFromPositionQuery('123', 1)) as $event) {
            $events[] = $event;
        }

        $this->assertCount(2, $events);
    }

    public function testReplyFromPositionGreaterThanZero(): void
    {
        $eventStore = new InMemoryEventStore();
        $this->storeNumberOfEvents($eventStore, '123', 4);

        $events = [];
        foreach ($eventStore->replyFromPosition(new ReplyFromPositionQuery('123', 2)) as $event) {
            $events[] = $event;
        }

        $this->assertCount(3, $events);
    }

    public function testReplyFromPositionWithAHigherPositionThanExisting(): void
    {
        $eventStore = new InMemoryEventStore();
        $this->storeNumberOfEvents($eventStore, '123', 1);

        $events = [];
        foreach ($eventStore->replyFromPosition(new ReplyFromPositionQuery('123', 10000)) as $event) {
            $events[] = $event;
        }

        $this->assertCount(0, $events);
    }
}
