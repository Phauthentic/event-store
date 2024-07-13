<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Tests;

use DateTimeImmutable;
use Phauthentic\EventStore\Event;
use Phauthentic\EventStore\EventInterface;
use Phauthentic\EventStore\EventStoreInterface;
use Phauthentic\EventStore\ReplyFromPositionQuery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 *
 */
abstract class AbstractEventStoreTestCase extends TestCase
{
    protected ?EventStoreInterface $eventStore;

    /**
     * @param $numberOfEvents
     * @return array
     */
    public function getEvents(?string $aggregateId = null, $numberOfEvents = 2): array
    {
        if ($aggregateId === null) {
            $aggregateId = Uuid::uuid4()->toString();
        }

        for ($i = 1; $i <= $numberOfEvents; $i++) {
            $events[] = new Event(
                aggregateId: $aggregateId,
                aggregateVersion: $i,
                event: 'event-' . $i,
                payload: '',
                createdAt: DateTimeImmutable::createFromFormat(
                    EventInterface::CREATED_AT_FORMAT,
                    '2023-12-12 12:00:00 12351'
                ),
                stream: 'test-stream'
            );
        }

        return $events;
    }

    /**
     * @param EventStoreInterface $eventStore
     * @param int $numberOfEvents
     */
    public function storeNumberOfEvents(
        EventStoreInterface $eventStore,
        ?string $aggregateId = null,
        int $numberOfEvents
    ): void {
        $this->getEvents($aggregateId, $numberOfEvents);

        foreach ($this->getEvents($aggregateId, $numberOfEvents) as $event) {
            $eventStore->storeEvent($event);
        }
    }

    public function testReplyFromPositionZero(): void
    {
        $aggregateId = Uuid::uuid4()->toString();
        $this->storeNumberOfEvents($this->eventStore, $aggregateId, 2);

        $events = [];
        foreach ($this->eventStore->replyFromPosition(new ReplyFromPositionQuery($aggregateId, 1)) as $event) {
            $events[] = $event;
        }

        $this->assertCount(2, $events);
    }

    public function testReplyFromPositionGreaterThanZero(): void
    {
        $aggregateId = Uuid::uuid4()->toString();
        $this->storeNumberOfEvents($this->eventStore, $aggregateId, 4);

        $events = [];
        foreach ($this->eventStore->replyFromPosition(new ReplyFromPositionQuery($aggregateId, 2)) as $event) {
            $events[] = $event;
        }

        $this->assertCount(3, $events);
    }

    public function testReplyFromPositionWithAHigherPositionThanExisting(): void
    {
        $aggregateId = Uuid::uuid4()->toString();
        $this->storeNumberOfEvents($this->eventStore, $aggregateId, 5);

        $events = [];
        foreach ($this->eventStore->replyFromPosition(new ReplyFromPositionQuery($aggregateId, 100000)) as $event) {
            $events[] = $event;
        }

        $this->assertCount(0, $events);
    }
}
