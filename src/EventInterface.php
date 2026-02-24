<?php

declare(strict_types=1);

namespace Phauthentic\EventStore;

use DateTimeImmutable;

/**
 * This DTO describes an event that was stored.
 */
interface EventInterface
{
    public const AGGREGATE_ID = 'aggregateId';
    public const STREAM = 'stream';
    public const VERSION = 'version';
    public const EVENT = 'event';
    public const CREATED_AT = 'createdAt';
    public const PAYLOAD = 'payload';
    public const CORRELATION_ID = 'correlationId';
    public const META_DATA = 'metaData';
    public const CREATED_AT_FORMAT = 'Y-m-d H:i:s u';

    /**
     * @return string
     */
    public function getStream(): ?string;

    /**
     * @return string
     */
    public function getAggregateId(): string;

    /**
     * @return int
     */
    public function getAggregateVersion(): int;

    /**
     * @return mixed
     */
    public function getPayload(): mixed;

    /**
     * @return string
     */
    public function getEvent(): string;

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable;

    /**
     * @return string|null
     */
    public function getCorrelationId(): ?string;

    /**
     * @return array<string, mixed>
     */
    public function getMetaData(): array;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
