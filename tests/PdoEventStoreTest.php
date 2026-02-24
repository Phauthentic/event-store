<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Tests;

use PDO;
use Phauthentic\EventStore\EventFactory;
use Phauthentic\EventStore\PdoEventStore;
use Phauthentic\EventStore\ReplyFromPositionQuery;
use Phauthentic\EventStore\Serializer\SerializeSerializer;

/**
 *
 */
class PdoEventStoreTest extends AbstractEventStoreTestCase
{
    private ?PDO $pdo = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->pdo = $this->createPdo();
        if ($this->pdo === null) {
            return;
        }

        $schemaFile = $this->isSqlite() ? './resources/event_store_sqlite.sql' : './resources/event_store.sql';
        $query = file_get_contents($schemaFile);
        if (!$this->isSqlite()) {
            $this->pdo->exec('USE test');
        }
        $this->pdo->exec($query);

        $this->eventStore = $this->createPdoEventStore();
    }

    protected function isSqlite(): bool
    {
        return extension_loaded('pdo_sqlite');
    }

    protected function createPdo(): ?PDO
    {
        if (extension_loaded('pdo_sqlite')) {
            $pdo = new PDO('sqlite::memory:');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        }

        if (!extension_loaded('pdo_mysql')) {
            return null;
        }

        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $dbname = getenv('DB_DATABASE') ?: 'test';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASSWORD') ?: 'changeme';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (\PDOException) {
            return null;
        }
    }

    protected function createPdoEventStore(): PdoEventStore
    {
        return new PdoEventStore(
            pdo: $this->pdo,
            serializer: new SerializeSerializer(),
            eventFactory: new EventFactory()
        );
    }

    public function testStoreEvent(): void
    {
        if ($this->pdo === null) {
            $this->markTestSkipped('No database driver available (pdo_sqlite or pdo_mysql required)');
        }

        $eventStore = $this->createPdoEventStore();
        $domainEvents = $this->getEvents();

        $eventStore->storeEvent($domainEvents[0]);
        $eventStore->storeEvent($domainEvents[1]);

        $events = [];
        foreach ($eventStore->replyFromPosition(new ReplyFromPositionQuery($domainEvents[0]->getAggregateId(), 1)) as $event) {
            $events[] = $event;
        }

        $this->assertCount(2, $events);
    }
}
