<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Tests;

use Phauthentic\EventStore\InMemoryEventStore;

/**
 *
 */
class InMemoryEventStoreTest extends AbstractEventStoreTestCase
{
    public function setUp(): void
    {
        $this->eventStore = new InMemoryEventStore();

        parent::setUp();
    }

    public function testReplyFromPositionWithNonExistentAggregate(): void
    {
        $eventStore = new InMemoryEventStore();

        $events = iterator_to_array($eventStore->replyFromPosition('non-existent-id'));

        $this->assertCount(0, $events);
    }
}
