<?php

namespace Tests;

use InvalidArgumentException;
use JesseGall\Resources\Resource;
use JesseGall\Resources\ResourceCollection;
use PHPUnit\Framework\TestCase;
use Tests\TestClasses\TestResource;

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
            public function getResources(): array
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

        $this->assertCount(0, $this->collection->getResources());
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

        $this->assertEquals($instance, $this->collection->getResources()[0]);
    }

    public function test_will_throw_exception_when_invalid_type_is_set()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->collection[0] = new Resource();
    }

    public function test_first_returns_first_resource()
    {
        $this->assertEquals($this->collection->getResources()[0], $this->collection->first());
    }

    public function test_all_returns_all_resources()
    {
        $this->assertEquals($this->collection->getResources(), $this->collection->all());
    }

    public function test_is_json_serializable()
    {
        $serialized = json_encode($this->collection);

        $deserialized = json_decode($serialized, true);

        $expected = array_map(fn(Resource $resource) => $resource->container(), $this->collection->getResources());

        $this->assertEquals($expected, $deserialized);
    }

    public function test_given_numbers_when_sort_then_sorted()
    {
        $unsorted = array_map(fn(int $i) => new Resource(['property' => $i]), [0, 2, 4, 3, 1]);

        $collection = new ResourceCollection(Resource::class, $unsorted);

        $sorted = $collection->sort('property');

        foreach ($sorted->all() as $index => $resource) {
            self::assertEquals($index, $resource->get('property'));
        }
    }

    public function test_given_strings_when_sort_then_sorted()
    {
        $unsorted = array_map(fn(string $i) => new Resource(['property' => $i]), ['aa', 'cc', 'ff', 'dd', 'bb']);

        $collection = new ResourceCollection(Resource::class, $unsorted);

        $sorted = $collection->sort('property');

        $expected = ['aa', 'bb', 'cc', 'dd', 'ff'];

        foreach ($sorted->all() as $index => $resource) {
            $this->assertEquals($expected[$index], $resource->get('property'));
        }
    }

    public function test_given_direction_desc_when_sort_then_sorted_descending()
    {
        $unsorted = array_map(fn(int $i) => new Resource(['property' => $i]), [0, 2, 4, 3, 1]);

        $collection = new ResourceCollection(Resource::class, $unsorted);

        $sorted = $collection->sort('property', 'desc');

        foreach ($sorted->all() as $index => $resource) {
            $this->assertEquals(4 - $index, $resource->get('property'));
        }
    }

    public function test_given_closure_when_sort_then_sorted()
    {
        $unsorted = array_map(fn(int $i) => new Resource(['property' => $i]), [0, 2, 4, 3, 1]);

        $collection = new ResourceCollection(Resource::class, $unsorted);

        $sorted = $collection->sort(fn(Resource $a, Resource $b) => $a->get('property') - $b->get('property'));

        foreach ($sorted->all() as $index => $resource) {
            $this->assertEquals($index, $resource->get('property'));
        }
    }

    public function test_when_filter_then_new_collection_returned()
    {
        $original = new ResourceCollection(Resource::class, []);

        $new = $original->filter(fn() => true);

        $this->assertNotSame($original, $new);
    }

    public function test_when_filter_then_filtered()
    {
        $unfiltered = new ResourceCollection(
            Resource::class,
            array_map(fn(int $i) => new Resource(['property' => $i]), [0, 1, 2, 3, 4])
        );

        $filtered = $unfiltered->filter(fn(Resource $resource) => ($resource->get('property') % 2) == 0);

        $this->assertCount(3, $filtered);
    }


}