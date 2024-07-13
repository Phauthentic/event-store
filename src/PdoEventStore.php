<?php

declare(strict_types=1);

namespace Phauthentic\EventStore;

use Generator;
use PDO;
use PDOStatement;
use Phauthentic\EventStore\Serializer\SerializerInterface;

/**
 * PDO Based Event Store
 *
 * This should work with
 * - MySQL & MariaBD
 * - PostgreSQL
 * - SQLite
 * - MS SQL Server
 */
class PdoEventStore implements EventStoreInterface
{
    public const EVENT_STORE_TABLE = 'event_store';
    public const STREAM = 'stream';
    public const AGGREGATE_ID = 'aggregate_id';
    public const VERSION = 'version';
    public const EVENT = 'event';
    public const PAYLOAD = 'payload';
    public const CREATED_AT = 'created_at';

    public function __construct(
        protected PDO $pdo,
        protected SerializerInterface $serializer,
        protected EventFactoryInterface $eventFactory,
        protected int $limit = 50,
        protected SQLDialect $sqlDialect = SQLDialect::Standard
    ) {
    }

    public function storeEvent(EventInterface $event): void
    {
        $values = [
            self::STREAM => $event->getStream(),
            self::AGGREGATE_ID => $event->getAggregateId(),
            self::VERSION => $event->getAggregateVersion(),
            self::EVENT => $event->getEvent(),
            self::PAYLOAD => $this->serializer->serialize($event->getPayload()),
            self::CREATED_AT => $event->getCreatedAt()->format(EventInterface::CREATED_AT_FORMAT),
        ];

        $columns = implode(', ', array_keys($values));
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        $sql = "INSERT INTO " . self::EVENT_STORE_TABLE . " ($columns) VALUES ($placeholders)";
        $statement = $this->pdo->prepare($sql);

        $statement->execute(array_values($values));
    }

    protected function getSqlQuery(): string
    {
        return "SELECT * FROM " . static::EVENT_STORE_TABLE . " 
                WHERE aggregate_id = :aggregateId AND version >= :position 
                ORDER BY version ASC 
                LIMIT " . $this->limit . " OFFSET :offset";
    }

    protected function getMsSqlQuery(): string
    {
        return "SELECT * FROM " . static::EVENT_STORE_TABLE . " 
                WHERE aggregate_id = :aggregateId AND version >= :position 
                ORDER BY version ASC 
                OFFSET :offset ROWS FETCH NEXT " . $this->limit . " ROWS ONLY";
    }

    protected function prepareReplyFromPositionStatement(): PDOStatement
    {
        return $this->sqlDialect === SQLDialect::MSSQL
            ? $this->pdo->prepare($this->getMsSqlQuery())
            : $this->pdo->prepare($this->getSqlQuery());
    }

    public function replyFromPosition(ReplyFromPositionQuery $fromPositionQuery): Generator
    {
        $offset = 0;
        $aggregateId = $fromPositionQuery->aggregateId;
        $position = $fromPositionQuery->position;

        $statement = $this->prepareReplyFromPositionStatement();
        $statement->bindParam(':aggregateId', $aggregateId);
        $statement->bindParam(':position', $position, PDO::PARAM_INT);

        do {
            $statement->bindParam(':offset', $offset, PDO::PARAM_INT);

            $statement->execute();
            $results = $statement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as $result) {
                yield $this->mapResultToEvent($result);
            }

            $offset += $this->limit;
        } while (!empty($results));
    }

    /**
     * @param array<string, mixed> $result
     * @return EventInterface
     */
    protected function mapResultToEvent(array $result): EventInterface
    {
        return $this->eventFactory->createEventFromArray([
            EventInterface::STREAM => $result[self::STREAM],
            EventInterface::AGGREGATE_ID => $result[self::AGGREGATE_ID],
            EventInterface::EVENT => $result[self::EVENT],
            EventInterface::PAYLOAD => $this->serializer->unserialize($result[self::PAYLOAD]),
            EventInterface::CREATED_AT => $result[self::CREATED_AT],
            EventInterface::VERSION => $result[self::VERSION],
        ]);
    }
}
