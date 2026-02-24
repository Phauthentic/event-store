# EventStoreInterface Implementation Guide

## Overview

The `EventStoreInterface` defines a contract for implementing an event store, allowing the storage and retrieval of events related to aggregates.

## Interface Methods

### `storeEvent`

Stores an event in the event store.

```php
public function storeEvent(EventInterface $event): void
{
    // Your implementation logic to store the event in the event store
}
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$event` | `EventInterface` | The event to be stored |

### `replyFromPosition`

Retrieves events for a given aggregate starting from a specified position. Returns an `Iterator` that yields `EventInterface` instances.

```php
public function replyFromPosition(ReplyFromPositionQuery $fromPositionQuery): Iterator
{
    // Your implementation logic to retrieve events
}
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$fromPositionQuery` | `ReplyFromPositionQuery` | Query with `aggregateId` and `position` |

**Note:** Position is 1-based. The value must be a positive integer; `ReplyFromPositionQuery` throws `EventStoreException` if position is 0 or less.

### Using ReplyFromPositionQuery

```php
use Phauthentic\EventStore\ReplyFromPositionQuery;

// Replay all events for aggregate 'order-123' starting from version 1
$query = new ReplyFromPositionQuery(aggregateId: 'order-123', position: 1);

// Replay from version 3 onward
$query = new ReplyFromPositionQuery(aggregateId: 'order-123', position: 3);

foreach ($eventStore->replyFromPosition($query) as $event) {
    // Process $event (EventInterface)
}
```

`position` defaults to `1` when omitted.

## EventInterface Contract

Events passed to `storeEvent` and returned from `replyFromPosition` must implement `EventInterface`. Use the `Event` class or map your domain events to it.

### Required Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `getStream()` | `?string` | Optional stream name |
| `getAggregateId()` | `string` | Aggregate identifier |
| `getAggregateVersion()` | `int` | Event version (1-based) |
| `getEvent()` | `string` | Event type name |
| `getPayload()` | `mixed` | Event payload |
| `getCreatedAt()` | `DateTimeImmutable` | When the event occurred |
| `getCorrelationId()` | `?string` | Optional correlation ID |
| `getMetaData()` | `array<string, mixed>` | Optional metadata |
| `toArray()` | `array<string, mixed>` | Array representation |

### Constants for Array Mapping

When mapping to/from arrays (e.g. with `EventFactory`), use these keys from `EventInterface`:

- `EventInterface::AGGREGATE_ID`, `STREAM`, `VERSION`, `EVENT`, `PAYLOAD`, `CREATED_AT`, `CORRELATION_ID`, `META_DATA`
- `EventInterface::CREATED_AT_FORMAT` = `'Y-m-d H:i:s u'` for date strings
