# Event Store Documentation

## General Usage

The event store provides two main operations:

1. **storeEvent** – Persist an `EventInterface` instance for an aggregate
2. **replyFromPosition** – Retrieve events for an aggregate starting from a given version (position)

Events are identified by `aggregateId` and ordered by `aggregateVersion`. You must map your domain events to the `Event` DTO (or implement `EventInterface`) before storing. Use `EventFactory` to create events from arrays when reading from persistence.

## Documentation Index

* [Architecture](Architecture.md)
* [Implementing your own store](Implementing-your-own-Store.md)
* Included Stores
  * [PDO](PDO-Event-Store.md)
  * [In Memory](In-Memory-Event-Store.md)
