<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Tests;

use Phauthentic\EventStore\Event;
use Phauthentic\EventStore\Exception\EventStoreException;
use Phauthentic\EventStore\InMemoryEventStore;
use Phauthentic\EventStore\ReplyFromPositionQuery;

/**
 *
 */
class InMemoryEventStoreTest extends AbstractEventStoreTestCase
{
    public function setUp(): void
    {
        $this->eventStore = new InMemoryEventStore();

        parent::setUp();
    }

    public function testReplyFromPositionWithNonExistentAggregate(): void
    {
        $eventStore = new InMemoryEventStore();

        $events = iterator_to_array($eventStore->replyFromPosition(
            new ReplyFromPositionQuery('non-existent-id', 1)
        ));

        $this->assertCount(0, $events);
    }

    public function testReplyFromPositionFiltersByPosition(): void
    {
        $eventStore = new InMemoryEventStore();
        $domainEvents = $this->getEvents();

        $eventStore->storeEvent($domainEvents[0]);
        $eventStore->storeEvent($domainEvents[1]);

        $events = iterator_to_array($eventStore->replyFromPosition(
            new ReplyFromPositionQuery($domainEvents[0]->getAggregateId(), 2)
        ));

        $this->assertCount(1, $events);
        $this->assertSame(2, $events[0]->getAggregateVersion());
    }

    public function testStoreEventThrowsOnDuplicateVersion(): void
    {
        $this->expectException(EventStoreException::class);
        $this->expectExceptionMessage('Duplicate event version');

        $eventStore = new InMemoryEventStore();
        $domainEvents = $this->getEvents();

        $eventStore->storeEvent($domainEvents[0]);
        $eventStore->storeEvent($domainEvents[0]);
    }

    public function testStoreEventThrowsOnInvalidVersion(): void
    {
        $this->expectException(EventStoreException::class);
        $this->expectExceptionMessage('Event version must be positive');

        $eventStore = new InMemoryEventStore();
        $domainEvents = $this->getEvents();
        $event = new Event(
            stream: 'test-stream',
            aggregateId: $domainEvents[0]->getAggregateId(),
            aggregateVersion: 0,
            event: 'invalid',
            payload: '',
            createdAt: $domainEvents[0]->getCreatedAt()
        );

        $eventStore->storeEvent($event);
    }
}
