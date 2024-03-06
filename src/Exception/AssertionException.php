<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Exception;

class AssertionException extends EventStoreException
{
    protected const ARRAY_HAS_MISSING_KEY = 'Array has a missing key %s';

    public static function arrayHasMissingKey(string $key): self
    {
        return new self(sprintf(
            static::ARRAY_HAS_MISSING_KEY,
            $key
        ));
    }
}
