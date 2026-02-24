# Event Store Architecture

The **phauthentic/event-store** is a PHP library providing an event sourcing abstraction. It stores domain events and allows replaying them by aggregate from a given version position. The design follows interface-based abstractions with pluggable storage backends and serialization.

## Architecture Diagram

```mermaid
flowchart TB
    subgraph clients [Application Layer]
        App[Application Code]
    end

    subgraph core [Core Contracts]
        ESI[EventStoreInterface]
        EFI[EventFactoryInterface]
        EI[EventInterface]
        SI[SerializerInterface]
    end

    subgraph implementations [Implementations]
        PDO[PdoEventStore]
        IM[InMemoryEventStore]
        EF[EventFactory]
        SS[SerializeSerializer]
    end

    subgraph models [Value Objects]
        Event[Event]
        RFPQ[ReplyFromPositionQuery]
        SQLD[SQLDialect]
    end

    App -->|storeEvent| ESI
    App -->|replyFromPosition| ESI
    ESI --> PDO
    ESI --> IM
    PDO --> EF
    PDO --> SI
    PDO --> EI
    IM --> EI
    EF --> Event
    RFPQ --> ESI
```

## Data Flow

### Store Event

```mermaid
sequenceDiagram
    participant App
    participant ES as EventStore
    participant Serializer
    participant DB as Database

    App->>ES: storeEvent(EventInterface)
    ES->>Serializer: serialize(payload), serialize(metaData)
    ES->>DB: INSERT INTO event_store
```

### Replay From Position

```mermaid
sequenceDiagram
    participant App
    participant ES as EventStore
    participant DB as Database
    participant Factory as EventFactory
    participant Serializer

    App->>ES: replyFromPosition(ReplyFromPositionQuery)
    loop Paginated (limit batches)
        ES->>DB: SELECT ... WHERE aggregate_id AND version >= position ORDER BY version LIMIT offset
        DB-->>ES: rows
        loop Each row
            ES->>Serializer: unserialize(payload), unserialize(metaData)
            ES->>Factory: createEventFromArray(row)
            Factory-->>ES: EventInterface
            ES-->>App: yield EventInterface
        end
    end
```

## Component Breakdown

### Core Interfaces

| Component | Purpose |
|-----------|---------|
| EventStoreInterface | Main contract: `storeEvent()` and `replyFromPosition()` returning an `Iterator` |
| EventInterface | DTO contract for stored events (aggregateId, version, event name, payload, createdAt, correlationId, metaData) |
| EventFactoryInterface | Creates `EventInterface` from array (used when hydrating from storage) |
| SerializerInterface | Serializes/unserializes payload and metadata for persistence |

### Implementations

| Component | Purpose |
|-----------|---------|
| PdoEventStore | SQL-backed store (MySQL, MariaDB, PostgreSQL, SQLite, MS SQL). Uses SerializerInterface for payload/metaData, EventFactoryInterface for hydration. Supports SQLDialect for MS SQL OFFSET/FETCH syntax. |
| InMemoryEventStore | In-memory store for testing. Validates version > 0 and rejects duplicate versions per aggregate. |
| EventFactory | Validates required keys, applies defaults (stream, correlationId, metaData), parses CREATED_AT from string. |
| SerializeSerializer | PHP serialize/unserialize with allowed_classes=true. See security warning for untrusted input. |

### Value Objects

| Component | Purpose |
|-----------|---------|
| Event | Immutable DTO implementing EventInterface |
| ReplyFromPositionQuery | Readonly query object: aggregateId + position (1-based). Validates position > 0. |
| SQLDialect | Enum: Standard (MySQL/PostgreSQL/SQLite) vs MSSQL for OFFSET/FETCH syntax |

## Database Schema

Single table `event_store` with:

- `id` (auto-increment)
- `stream`, `aggregate_id`, `version`, `event`, `payload`, `created_at`, `correlation_id`, `meta_data`
- Unique constraint on `(aggregate_id, version)` to enforce optimistic concurrency
- Composite index on `(aggregate_id, version)` for replay queries
