<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Tests;

use PDO;
use Phauthentic\EventStore\EventFactory;
use Phauthentic\EventStore\PdoEventStore;
use Phauthentic\EventStore\ReplyFromPositionQuery;
use Phauthentic\EventStore\Serializer\SerializeSerializer;
use Ramsey\Uuid\Uuid;

/**
 *
 */
class PdoEventStoreTest extends AbstractEventStoreTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $pdo = $this->createPdo();
        $query = file_get_contents('./resources/event_store.sql');
        $pdo->query('use test');
        $pdo->query($query);
    }

    protected function createPdo(): PDO
    {
        $host = getenv('DB_HOST') ?: 'mysql-container';
        $dbname = getenv('DB_DATABASE') ?: 'test';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASSWORD') ?: 'changeme';

        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);

        // Set PDO attributes for error handling and fetch mode
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }

    protected function createPdoEventStore(): PdoEventStore
    {
        return new PdoEventStore(
            pdo: $this->createPdo(),
            serializer: new SerializeSerializer(),
            eventFactory: new EventFactory()
        );
    }

    public function testReplyFromPositionZero(): void
    {
        $aggregateId = Uuid::uuid4()->toString();
        $eventStore = $this->createPdoEventStore();
        $this->storeNumberOfEvents($eventStore, $aggregateId, 2);

        $events = [];
        foreach ($eventStore->replyFromPosition(new ReplyFromPositionQuery($aggregateId,)) as $event) {
            $events[] = $event;
        }

        $this->assertCount(2, $events);
    }

    public function testReplyFromPositionGreaterThanZero(): void
    {
        $aggregateId = Uuid::uuid4()->toString();
        $eventStore = $this->createPdoEventStore();
        $this->storeNumberOfEvents($eventStore, $aggregateId, 4);

        $events = [];
        foreach ($eventStore->replyFromPosition(new ReplyFromPositionQuery($aggregateId, 2)) as $event) {
            $events[] = $event;
        }

        $this->assertCount(3, $events);
    }

    public function testReplyFromPositionWithAHigherPositionThanExisting(): void
    {
        $aggregateId = Uuid::uuid4()->toString();
        $eventStore = $this->createPdoEventStore();
        $this->storeNumberOfEvents($eventStore, $aggregateId, 5);

        $events = [];
        foreach ($eventStore->replyFromPosition(new ReplyFromPositionQuery($aggregateId, 100000)) as $event) {
            $events[] = $event;
        }

        $this->assertCount(0, $events);
    }
}
