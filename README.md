# Event Store

**⚠ Do not use it in production! This is still in development!**

This is an event store abstraction for the [Phauthentic Event Sourcing Library](https://github.com/phauthentic/event-sourcing). It provides a simple interface for storing and retrieving domain events by aggregate, with pluggable backends for different storage engines.

## Requirements

- PHP 8.2 or higher

## Installation

```sh
composer require phauthentic/event-store
```

## Features

- **EventStoreInterface** – Contract for storing and replaying events by aggregate
- **PDO Event Store** – SQL-backed store (MySQL, MariaDB, PostgreSQL, SQLite, MS SQL Server)
- **In-Memory Event Store** – For testing, prototyping, and demos
- **EventFactory** – Create events from arrays and convert events to arrays
- **Serialization** – Pluggable serializers for payload and metadata

## Quick Start

```php
use Phauthentic\EventStore\Event;
use Phauthentic\EventStore\InMemoryEventStore;
use Phauthentic\EventStore\ReplyFromPositionQuery;

$eventStore = new InMemoryEventStore();

$event = new Event(
    aggregateId: 'order-123',
    aggregateVersion: 1,
    event: 'OrderCreated',
    payload: ['amount' => 99.99],
    createdAt: new \DateTimeImmutable()
);

$eventStore->storeEvent($event);

foreach ($eventStore->replyFromPosition(new ReplyFromPositionQuery('order-123', 1)) as $storedEvent) {
    // Process event...
}
```

## Documentation

Please start by reading [documentation](docs/Index.md) in this repository.

## License

Copyright Florian Krämer

Licensed under the [MIT license](license.txt).
