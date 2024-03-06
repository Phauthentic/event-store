<?php

declare(strict_types=1);

namespace Phauthentic\EventStore\Tests\Serializer;

use Phauthentic\EventStore\Serializer\SerializeSerializer;
use PHPUnit\Framework\TestCase;
use stdClass;

class SerializerSerializerTest extends TestCase
{
    protected SerializeSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new SerializeSerializer();
    }

    public function testSerializer(): void
    {
        $object = new stdClass();
        $result = $this->serializer->serialize($object);

        $this->assertIsString($result);

        $result = $this->serializer->unserialize($result);
        $this->assertEquals($object, $result);
    }
}
