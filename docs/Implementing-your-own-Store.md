# EventStoreInterface Implementation Guide

## Overview

The `EventStoreInterface` defines a contract for implementing an event store, allowing the storage and retrieval of events related to aggregates.

## Interface Methods

### `storeEvent`

This method is responsible for storing an event in the event store. It takes an instance of EventInterface as a parameter.

```php
// Example Implementation
public function storeEvent(EventInterface $event): void
{
    // Your implementation logic to store the event in the event store
}
```

Parameters

* $event (EventInterface): The event to be stored in the event store.

### `replyFromPosition`

This method retrieves events for a given aggregate starting from a specified position. It returns an Iterator that allows iterating over the retrieved events.

```php
public function replyFromPosition(string $aggregateId, int $position = 0): Iterator;
```

Parameters

* $aggregateId (string): The identifier of the aggregate for which events are to be retrieved.
* $position (int, optional): The starting position from which to retrieve events. Defaults to 0.
