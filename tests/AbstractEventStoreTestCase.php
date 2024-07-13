<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Tests;

use DateTimeImmutable;
use Phauthentic\EventStore\Event;
use Phauthentic\EventStore\EventInterface;
use Phauthentic\EventStore\EventStoreInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 *
 */
class AbstractEventStoreTestCase extends TestCase
{
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
}
