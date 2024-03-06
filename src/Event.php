<?php

declare(strict_types=1);

namespace Phauthentic\EventStore;

use DateTime;
use DateTimeImmutable;
use Phauthentic\EventStore\Exception\AssertionException;

/**
 * Event Store internal Event
 *
 * This is a DTO for the events in the event store.
 *
 * You must map your systems events to this event so the event store can store them!
 */
class Event implements EventInterface
{
    /**
     * @param string $aggregateId
     * @param int $aggregateVersion
     * @param string $event
     * @param mixed $payload
     * @param DateTimeImmutable $createdAt
     * @param string $correlationId
     * @param null|string $stream
     * @param array<string, mixed> $metaData
     * @return void
     */
    public function __construct(
        protected string $aggregateId,
        protected int $aggregateVersion,
        protected string $event,
        protected mixed $payload,
        protected DateTimeImmutable $createdAt,
        protected string $correlationId = '',
        protected ?string $stream = null,
        protected array $metaData = []
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getStream(): ?string
    {
        return $this->stream;
    }

    /**
     * @inheritDoc
     */
    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    /**
     * @inheritDoc
     */
    public function getAggregateVersion(): int
    {
        return $this->aggregateVersion;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): mixed
    {
        return $this->payload;
    }

    /**
     * @inheritDoc
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCorrelationId(): ?string
    {
        return $this->correlationId;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetaData(): array
    {
        return $this->metaData;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            EventInterface::STREAM => $this->getStream(),
            EventInterface::AGGREGATE_ID => $this->getAggregateId(),
            EventInterface::VERSION => $this->getAggregateVersion(),
            EventInterface::EVENT => $this->getEvent(),
            EventInterface::PAYLOAD => $this->getPayload(),
            EventInterface::CREATED_AT => $this->getCreatedAt()->format(EventInterface::CREATED_AT_FORMAT),
            EventInterface::CORRELATION_ID => $this->getCorrelationId()
        ];
    }
}
