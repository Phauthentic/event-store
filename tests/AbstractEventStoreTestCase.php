<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Tests;

use DateTimeImmutable;
use Phauthentic\EventStore\Event;
use Phauthentic\EventStore\EventInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 *
 */
class AbstractEventStoreTestCase extends TestCase
{
    public function getEvents($numberOfEvents = 2): array
    {
        $uuid = Uuid::uuid4()->toString();

        for ($i = 1; $i <= $numberOfEvents; $i++) {
            $events[] = new Event(
                aggregateId: $uuid,
                aggregateVersion: $i,
                event: 'created',
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
}
