<?php

declare(strict_types=1);

namespace Phauthentic\EventStore;

use Phauthentic\EventStore\Exception\EventStoreException;

/**
 *
 */
readonly class ReplyFromPositionQuery
{
    public function __construct(
        public string $aggregateId,
        public int $position = 1
    ) {
        $this->assertPositivePosition($this->position);
    }

    protected function assertPositivePosition(int $position): void
    {
        if ($position <= 0) {
            throw new EventStoreException('Position must be a positive integer');
        }
    }
}
