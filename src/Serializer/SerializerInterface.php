<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Serializer;

interface SerializerInterface
{
    public function serialize(mixed $mixed): string;

    public function unserialize(string $data): mixed;
}
