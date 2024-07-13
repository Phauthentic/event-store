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
}
