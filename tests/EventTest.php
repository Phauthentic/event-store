<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Tests;

use DateTimeImmutable;
use Phauthentic\EventStore\Event;
use Phauthentic\EventStore\EventInterface;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class EventTest extends TestCase
{
    public function testEventCreation(): void
    {
        $aggregateId = 'aggregate-id-1';
        $aggregateVersion = 1;
        $event = 'UserRegistered';
        $payload = ['user_id' => 123];
        $createdAt = new DateTimeImmutable('now');
        $correlationId = 'correlation-id-1';
        $stream = 'user-stream';
        $metaData = ['key' => 'value'];

        $eventObject = new Event(
            $aggregateId,
            $aggregateVersion,
            $event,
            $payload,
            $createdAt,
            $correlationId,
            $stream,
            $metaData
        );

        $this->assertInstanceOf(EventInterface::class, $eventObject);
        $this->assertEquals($aggregateId, $eventObject->getAggregateId());
        $this->assertEquals($aggregateVersion, $eventObject->getAggregateVersion());
        $this->assertEquals($event, $eventObject->getEvent());
        $this->assertEquals($payload, $eventObject->getPayload());
        $this->assertEquals($createdAt, $eventObject->getCreatedAt());
        $this->assertEquals($correlationId, $eventObject->getCorrelationId());
        $this->assertEquals($stream, $eventObject->getStream());
        $this->assertEquals($metaData, $eventObject->getMetaData());
    }

    public function testDefaultValues(): void
    {
        $aggregateId = 'aggregate-id-1';
        $aggregateVersion = 1;
        $event = 'UserRegistered';
        $payload = ['user_id' => 123];
        $createdAt = new DateTimeImmutable('now');

        $eventObject = new Event(
            $aggregateId,
            $aggregateVersion,
            $event,
            $payload,
            $createdAt
        );

        $this->assertEquals('', $eventObject->getCorrelationId());
        $this->assertNull($eventObject->getStream());
        $this->assertEquals([], $eventObject->getMetaData());
    }

    public function testToArray(): void
    {
        $aggregateId = 'aggregate-id-1';
        $aggregateVersion = 1;
        $event = 'UserRegistered';
        $payload = ['user_id' => 123];
        $createdAt = new DateTimeImmutable('now');
        $correlationId = 'correlation-id-1';
        $stream = 'user-stream';
        $metaData = ['key' => 'value'];

        $eventObject = new Event(
            $aggregateId,
            $aggregateVersion,
            $event,
            $payload,
            $createdAt,
            $correlationId,
            $stream,
            $metaData
        );

        $expectedArray = [
            EventInterface::STREAM => $stream,
            EventInterface::AGGREGATE_ID => $aggregateId,
            EventInterface::VERSION => $aggregateVersion,
            EventInterface::EVENT => $event,
            EventInterface::PAYLOAD => $payload,
            EventInterface::CREATED_AT => $createdAt->format(EventInterface::CREATED_AT_FORMAT),
            EventInterface::CORRELATION_ID => $correlationId
        ];

        $this->assertEquals($expectedArray, $eventObject->toArray());
    }
}
