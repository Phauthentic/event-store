<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Tests;

use Phauthentic\EventStore\Exception\EventStoreException;
use Phauthentic\EventStore\ReplyFromPositionQuery;

/**
 *
 */
class ReplyFromPositionQueryTest extends AbstractEventStoreTestCase
{
    public function testNegativePosition(): void
    {
        $this->expectException(EventStoreException::class);

        new ReplyFromPositionQuery('foo', 0); // Test for zero
    }

    public function testZeroPosition(): void
    {
        $this->expectException(EventStoreException::class);

        new ReplyFromPositionQuery('foo', 0);
    }

    public function testPositivePosition(): void
    {
        $query = new ReplyFromPositionQuery('foo', 1);
        $this->assertInstanceOf(ReplyFromPositionQuery::class, $query);
    }
}
