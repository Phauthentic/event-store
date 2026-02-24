<?php

declare(strict_types=1);

namespace Phauthentic\EventStore;

use Generator;
use PDO;
use PDOException;
use PDOStatement;
use Phauthentic\EventStore\Exception\EventStoreException;
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
    public const CORRELATION_ID = 'correlation_id';
    public const META_DATA = 'meta_data';

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
        $correlationId = $event->getCorrelationId();
        $metaData = $event->getMetaData();

        $values = [
            self::STREAM => $event->getStream(),
            self::AGGREGATE_ID => $event->getAggregateId(),
            self::VERSION => $event->getAggregateVersion(),
            self::EVENT => $event->getEvent(),
            self::PAYLOAD => $this->serializer->serialize($event->getPayload()),
            self::CREATED_AT => $event->getCreatedAt()->format(EventInterface::CREATED_AT_FORMAT),
            self::CORRELATION_ID => $correlationId !== null && $correlationId !== '' ? $correlationId : null,
            self::META_DATA => $metaData !== [] ? $this->serializer->serialize($metaData) : null,
        ];

        $columns = implode(', ', array_keys($values));
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        $sql = "INSERT INTO " . self::EVENT_STORE_TABLE . " ($columns) VALUES ($placeholders)";
        $statement = $this->pdo->prepare($sql);

        try {
            $statement->execute(array_values($values));
        } catch (PDOException $e) {
            if ($this->isDuplicateKeyError($e)) {
                throw new EventStoreException(sprintf(
                    'Duplicate event version %d for aggregate %s',
                    $event->getAggregateVersion(),
                    $event->getAggregateId()
                ), 0, $e);
            }
            throw $e;
        }
    }

    protected function isDuplicateKeyError(PDOException $e): bool
    {
        $code = $e->getCode();
        $message = $e->getMessage();

        return $code === '23000'
            || $code === 23000
            || str_contains($message, '23000')
            || str_contains($message, '1062') // MySQL duplicate key
            || str_contains($message, '23505') // PostgreSQL unique violation
            || str_contains($message, 'UNIQUE constraint failed'); // SQLite
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
        $payload = $this->serializer->unserialize($result[self::PAYLOAD]);
        $metaData = isset($result[self::META_DATA])
            ? $this->serializer->unserialize($result[self::META_DATA])
            : [];

        return $this->eventFactory->createEventFromArray([
            EventInterface::STREAM => $result[self::STREAM],
            EventInterface::AGGREGATE_ID => $result[self::AGGREGATE_ID],
            EventInterface::EVENT => $result[self::EVENT],
            EventInterface::PAYLOAD => $payload,
            EventInterface::CREATED_AT => $result[self::CREATED_AT],
            EventInterface::VERSION => (int) $result[self::VERSION],
            EventInterface::CORRELATION_ID => $result[self::CORRELATION_ID] ?? '',
            EventInterface::META_DATA => $metaData,
        ]);
    }
}
