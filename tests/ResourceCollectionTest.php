<?php

namespace Test;

use InvalidArgumentException;
use JesseGall\Resources\Resource;
use JesseGall\Resources\ResourceCollection;
use PHPUnit\Framework\TestCase;
use Test\TestClasses\TestResource;

class ResourceCollectionTest extends TestCase
{

    private ResourceCollection $collection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collection = new class(TestResource::class, [
            new TestResource(),
            new TestResource(),
            new TestResource(),
        ]) extends ResourceCollection {
            public function getResource(): array
            {
                return $this->resources;
            }
        };
    }

    public function test_exception_is_thrown_when_not_all_resources_are_of_the_expected_type()
    {
        $this->expectException(InvalidArgumentException::class);

        new ResourceCollection(TestResource::class, [
            new TestResource(),
            new TestResource(),
            new Resource() // Must throw exception
        ]);
    }

    public function test_has_expected_count()
    {
        $this->assertCount(3, $this->collection);
    }

    public function test_can_be_indexed()
    {
        $this->assertInstanceOf(TestResource::class, $this->collection[0]);
    }

    public function test_can_be_iterated()
    {
        $this->assertIsIterable($this->collection);

        foreach ($this->collection as $resource) {
            $this->assertInstanceOf(TestResource::class, $resource);
        }
    }

    public function test_can_unset_with_index()
    {
        unset($this->collection[0]);
        unset($this->collection[1]);
        unset($this->collection[2]);

        $this->assertCount(0, $this->collection->getResource());
    }

    public function test_can_isset_with_index()
    {
        $this->assertTrue(isset($this->collection[0]));
        $this->assertTrue(isset($this->collection[1]));
        $this->assertTrue(isset($this->collection[2]));
    }

    public function test_can_set_with_index()
    {
        $instance = new TestResource();

        $this->collection[0] = $instance;

        $this->assertEquals($instance, $this->collection->getResource()[0]);
    }

    public function test_will_throw_exception_when_invalid_type_is_set()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->collection[0] = new Resource();
    }


}