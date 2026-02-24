<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Serializer;

/**
 * PHP serialize/unserialize implementation.
 *
 * WARNING: This serializer uses PHP's native serialize/unserialize with allowed_classes=true,
 * which permits deserializing any PHP class. If event payloads or metadata come from untrusted
 * sources (e.g. user input, external APIs), this enables object injection attacks.
 * Consider using a JSON-based serializer for untrusted or externally-sourced data.
 */
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
