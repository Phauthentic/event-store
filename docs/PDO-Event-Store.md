# PDO Event Store

The `PdoEventStore` persists events in a SQL database. It supports MySQL, MariaDB, PostgreSQL, SQLite, and MS SQL Server.

## Setup

### Constructor

```php
use Phauthentic\EventStore\EventFactory;
use Phauthentic\EventStore\PdoEventStore;
use Phauthentic\EventStore\Serializer\SerializeSerializer;

$eventStore = new PdoEventStore(
    pdo: $pdo,
    serializer: new SerializeSerializer(),
    eventFactory: new EventFactory(),
    limit: 50,                    // optional, default 50 (batch size for replay)
    sqlDialect: SQLDialect::Standard  // optional, use SQLDialect::MSSQL for MS SQL Server
);
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `pdo` | `PDO` | Yes | PDO connection instance |
| `serializer` | `SerializerInterface` | Yes | Serializes/deserializes payload and metadata |
| `eventFactory` | `EventFactoryInterface` | Yes | Creates `EventInterface` from database rows |
| `limit` | `int` | No | Batch size when replaying events (default: 50) |
| `sqlDialect` | `SQLDialect` | No | `Standard` (MySQL, PostgreSQL, SQLite) or `MSSQL` |

### Database Schema

Create the `event_store` table before using the store. Schema files are provided:

- **MySQL / MariaDB / PostgreSQL**: [resources/event_store.sql](../resources/event_store.sql)
- **SQLite**: [resources/event_store_sqlite.sql](../resources/event_store_sqlite.sql)

Table name: `event_store`

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT / INTEGER | Primary key, auto-increment |
| `stream` | VARCHAR(128) NULL | Optional stream name |
| `aggregate_id` | CHAR(36) NOT NULL | Aggregate identifier |
| `version` | INT NOT NULL | Event version (1-based) |
| `event` | VARCHAR(255) NOT NULL | Event type name |
| `payload` | TEXT NOT NULL | Serialized event payload |
| `created_at` | VARCHAR(128) NOT NULL | Timestamp (format: `Y-m-d H:i:s u`) |
| `correlation_id` | VARCHAR(255) NULL | Optional correlation ID |
| `meta_data` | TEXT NULL | Serialized metadata |

Unique constraint: `(aggregate_id, version)`

## Full Example

```php
use PDO;
use Phauthentic\EventStore\Event;
use Phauthentic\EventStore\EventFactory;
use Phauthentic\EventStore\PdoEventStore;
use Phauthentic\EventStore\ReplyFromPositionQuery;
use Phauthentic\EventStore\Serializer\SerializeSerializer;

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec(file_get_contents(__DIR__ . '/../resources/event_store_sqlite.sql'));

$eventStore = new PdoEventStore(
    pdo: $pdo,
    serializer: new SerializeSerializer(),
    eventFactory: new EventFactory()
);

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

## Using the PDO Store with ORMs

### Doctrine

```php
use Doctrine\ORM\EntityManagerInterface;

// Assuming $entityManager is your EntityManager instance
$connection = $entityManager->getConnection();
$pdo = $connection->getWrappedConnection();
```

### Laravel

```php
use Illuminate\Support\Facades\DB;

$pdo = DB::connection()->getPdo();
```

### CakePHP

```php
use Cake\Datasource\ConnectionManager;

$connection = ConnectionManager::get('default');
$pdo = $connection->getDriver()->getConnection();
```
