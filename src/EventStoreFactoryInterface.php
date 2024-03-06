<?php

declare(strict_types=1);

namespace Phauthentic\EventStore;

/**
 *
 */
interface EventStoreFactoryInterface
{
    public function createEventStore(): EventStoreInterface;
}
