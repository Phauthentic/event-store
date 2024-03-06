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
    public function getEvents(): array
    {
        $uuid = Uuid::uuid4()->toString();

        return [
            new Event(
                stream: 'test-stream',
                aggregateId: $uuid,
                aggregateVersion: 1,
                event: 'created',
                payload: '',
                createdAt: DateTimeImmutable::createFromFormat(
                    EventInterface::CREATED_AT_FORMAT,
                    '2023-12-12 12:00:00 12351'
                )
            ),
            new Event(
                stream: 'test-stream',
                aggregateId: $uuid,
                aggregateVersion: 2,
                event: 'edited',
                payload: '',
                createdAt: DateTimeImmutable::createFromFormat(
                    EventInterface::CREATED_AT_FORMAT,
                    '2023-12-12 12:00:01 12351'
                )
            )
        ];
    }
}
