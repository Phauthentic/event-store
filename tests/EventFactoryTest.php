<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Tests;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Phauthentic\EventStore\Event;
use Phauthentic\EventStore\EventFactory;
use Phauthentic\EventStore\EventInterface;
use Phauthentic\EventStore\Exception\AssertionException;

class EventFactoryTest extends TestCase
{
    protected const TEST_DATE = '2022-01-01 12:00:00 249763';

    public function testCreateEventFromArray(): void
    {
        $eventArray = [
            EventInterface::AGGREGATE_ID => '123',
            EventInterface::VERSION => 1,
            EventInterface::EVENT => 'some_event',
            EventInterface::PAYLOAD => ['key' => 'value'],
            EventInterface::CREATED_AT => static::TEST_DATE,
        ];

        $eventFactory = new EventFactory();
        $event = $eventFactory->createEventFromArray($eventArray);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals('123', $event->getAggregateId());
        $this->assertEquals(1, $event->getAggregateVersion());
        $this->assertEquals('some_event', $event->getEvent());
        $this->assertEquals(['key' => 'value'], $event->getPayload());
        $this->assertEquals(static::TEST_DATE, $event->getCreatedAt()->format(EventInterface::CREATED_AT_FORMAT));
    }

    public function testArrayFromEvent(): void
    {
        $event = new Event(
            aggregateId: '123',
            aggregateVersion: 1,
            event: 'some_event',
            payload: ['key' => 'value'],
            createdAt: DateTimeImmutable::createFromFormat(
                EventInterface::CREATED_AT_FORMAT,
                static::TEST_DATE
            ),
            stream: null
        );

        $eventFactory = new EventFactory();
        $eventArray = $eventFactory->arrayFromEvent($event);

        $this->assertIsArray($eventArray);
        $this->assertArrayHasKey(EventInterface::STREAM, $eventArray);
        $this->assertEquals($event->getStream(), $eventArray[EventInterface::STREAM]);
        $this->assertEquals('123', $eventArray[EventInterface::AGGREGATE_ID]);
        $this->assertEquals(1, $eventArray[EventInterface::VERSION]);
        $this->assertEquals('some_event', $eventArray[EventInterface::EVENT]);
        $this->assertEquals(['key' => 'value'], $eventArray[EventInterface::PAYLOAD]);
        $this->assertEquals($event->getCreatedAt(), $eventArray[EventInterface::CREATED_AT]);
    }

    public function testCreateEventFromArrayWithMissingKey(): void
    {
        $this->expectException(AssertionException::class);
        $this->expectExceptionMessage('Array has a missing key aggregateId');

        $eventArray = [
            // Missing AGGREGATE_ID key
            EventInterface::VERSION => 1,
            EventInterface::EVENT => 'some_event',
            EventInterface::PAYLOAD => ['key' => 'value'],
            EventInterface::CREATED_AT => static::TEST_DATE,
        ];

        $eventFactory = new EventFactory();
        $eventFactory->createEventFromArray($eventArray);
    }
}
