<?php

declare(strict_types=1);

namespace Phauthentic\EventStore;

/**
 *
 */
interface EventFactoryInterface
{
    /**
     * @param array<string, mixed> $data
     * @return EventInterface
     */
    public function createEventFromArray(array $data): EventInterface;

    /**
     * @param EventInterface $event
     * @return array<string, mixed>
     */
    public function arrayFromEvent(EventInterface $event): array;
}
