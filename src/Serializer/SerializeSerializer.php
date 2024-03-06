<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Serializer;

class SerializeSerializer implements SerializerInterface
{
    public function serialize(mixed $mixed): string
    {
        return serialize($mixed);
    }

    public function unserialize(string $data): mixed
    {
        return unserialize($data, ['allowed_classes' => true]);
    }
}
