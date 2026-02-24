# In-Memory Event Store

The `InMemoryEventStore` keeps events in memory. It has no external dependencies and is ideal for testing, prototyping, and demos. Data is lost when the process ends.

## When to Use

- **Testing** – Unit and integration tests without a database
- **Prototyping** – Quick experimentation with event-sourced flows
- **Demos** – Simple examples and tutorials

## Constructor

No dependencies are required:

```php
use Phauthentic\EventStore\InMemoryEventStore;

$eventStore = new InMemoryEventStore();
```

## Example

```php
use DateTimeImmutable;
use Phauthentic\EventStore\Event;
use Phauthentic\EventStore\InMemoryEventStore;
use Phauthentic\EventStore\ReplyFromPositionQuery;

$eventStore = new InMemoryEventStore();

$event1 = new Event(
    aggregateId: 'order-123',
    aggregateVersion: 1,
    event: 'OrderCreated',
    payload: ['amount' => 99.99],
    createdAt: new DateTimeImmutable()
);

$event2 = new Event(
    aggregateId: 'order-123',
    aggregateVersion: 2,
    event: 'OrderPaid',
    payload: ['paymentId' => 'pay-456'],
    createdAt: new DateTimeImmutable()
);

$eventStore->storeEvent($event1);
$eventStore->storeEvent($event2);

foreach ($eventStore->replyFromPosition(new ReplyFromPositionQuery('order-123', 1)) as $event) {
    echo $event->getEvent() . "\n";
}
// Output: OrderCreated, OrderPaid
```

## Behavior

- **Version must be positive** – Storing an event with `aggregateVersion` &lt; 1 throws `EventStoreException`
- **Duplicate versions** – Storing two events with the same `aggregateId` and `aggregateVersion` throws `EventStoreException`
- **Non-existent aggregate** – `replyFromPosition` for an unknown aggregate returns an empty iterator
