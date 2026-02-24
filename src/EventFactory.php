<?php

declare(strict_types=1);

namespace Phauthentic\EventStore;

use DateTimeImmutable;
use Phauthentic\EventStore\Exception\AssertionException;
use Phauthentic\EventStore\Exception\EventStoreException;

class EventFactory implements EventFactoryInterface
{
    /**
     * @param array<string, mixed> $array
     * @return void
     * @throws AssertionException
     */
    protected function assertArrayKeys(array $array): void
    {
        $this->assertArrayHasKey($array, EventInterface::AGGREGATE_ID);
        $this->assertArrayHasKey($array, EventInterface::PAYLOAD);
        $this->assertArrayHasKey($array, EventInterface::EVENT);
        $this->assertArrayHasKey($array, EventInterface::CREATED_AT);
        $this->assertArrayHasKey($array, EventInterface::VERSION);
    }

    /**
     * @param array<string, mixed> $array
     * @param string $key
     * @return void
     * @throws AssertionException
     */
    protected function assertArrayHasKey(array $array, string $key): void
    {
        if (!isset($array[$key])) {
            throw AssertionException::arrayHasMissingKey($key);
        }
    }

    protected function createdAtFromString(string $string): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat(
            EventInterface::CREATED_AT_FORMAT,
            $string
        );

        if ($date === false) {
            throw new EventStoreException(sprintf(
                'Could not create date from string `%s`',
                $string
            ));
        }

        return $date;
    }

    /**
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     * @throws EventStoreException
     */
    protected function setDefaults(array $array): array
    {
        if (!isset($array[EventInterface::STREAM])) {
            $array[EventInterface::STREAM] = null;
        }

        if (!isset($array[EventInterface::CREATED_AT])) {
            // @codeCoverageIgnoreStart - unreachable: assertArrayKeys requires CREATED_AT
            $array[EventInterface::CREATED_AT] = new DateTimeImmutable();
            // @codeCoverageIgnoreEnd
        }

        if (is_string($array[EventInterface::CREATED_AT])) {
            $array[EventInterface::CREATED_AT] = $this->createdAtFromString($array[EventInterface::CREATED_AT]);
        }

        return $array;
    }

    public function createEventFromArray(array $array): EventInterface
    {
        $this->assertArrayKeys($array);
        $array = $this->setDefaults($array);

        return new Event(
            stream: $array[EventInterface::STREAM],
            aggregateId: $array[EventInterface::AGGREGATE_ID],
            aggregateVersion: $array[EventInterface::VERSION],
            event: $array[EventInterface::EVENT],
            payload: $array[EventInterface::PAYLOAD],
            createdAt: $array[EventInterface::CREATED_AT]
        );
    }

    /**
     * @param EventInterface $event
     * @return array<string, mixed>
     */
    public function arrayFromEvent(EventInterface $event): array
    {
        return [
            EventInterface::VERSION => $event->getAggregateVersion(),
            EventInterface::STREAM => $event->getStream(),
            EventInterface::AGGREGATE_ID => $event->getAggregateId(),
            EventInterface::EVENT => $event->getEvent(),
            EventInterface::PAYLOAD => $event->getPayload(),
            EventInterface::CREATED_AT => $event->getCreatedAt(),
        ];
    }
}
