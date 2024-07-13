<?php

declare(strict_types=1);

namespace Phauthentic\EventStore;

use Iterator;

interface EventStoreInterface
{
    public function storeEvent(EventInterface $event): void;

    public function replyFromPosition(ReplyFromPositionQuery $fromPositionQuery): Iterator;
}
