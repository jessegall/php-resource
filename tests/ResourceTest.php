<?php

namespace Tests;

use JesseGall\Resources\Resource;
use JesseGall\Resources\ResourceCollection;
use PHPUnit\Framework\TestCase;
use Tests\TestClasses\TestResource;
use Tests\TestClasses\TestResourceTwo;

class ResourceTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->resource = new TestResource();
    }

    public function test_can_set_value()
    {
        $this->resource->set('nested.property', 'nested value');

        $this->assertEquals(
            'nested value',
            $this->resource->getContainer()['nested']['property']
        );
    }

    public function test_can_be_created_with_static_method()
    {
        $this->assertInstanceOf(TestResource::class, TestResource::new());
    }

    public function test_can_create_a_collection_with_static_method()
    {
        $this->assertInstanceOf(ResourceCollection::class, $collection = TestResource::collection());

        $this->assertEquals(TestResource::class, $collection->getType());
    }

    public function test_map_to_resource_creates_expected_resource()
    {
        $this->assertInstanceOf(TestResourceTwo::class, $this->resource->getRelationSingle());
    }

    public function test_map_to_resource_returns_a_collection_of_expected_resources()
    {
        $actual = $this->resource->getRelationList();

        $this->assertCount(3, $actual);

        foreach ($actual as $resource) {
            $this->assertInstanceOf(TestResourceTwo::class, $resource, true);
        }
    }

    public function test_map_to_resource_returns_null_when_item_is_null()
    {
        $this->assertNull($this->resource->getRelationMissing());
    }

    public function test_relation_is_returned_from_loaded_relations_when_relation_is_loaded()
    {
        $resource = $this->createPartialMock(TestResource::class, ['getRelation']);

        $resource->setRelation('relationSingle', new TestResourceTwo());

        $resource->method('getRelation')->willReturn(new TestResourceTwo());

        $resource->expects($this->once())
                 ->method('getRelation')
                 ->with('relationSingle')
                 ->willReturn(new TestResourceTwo());

        $resource->getRelationSingle();
    }

    public function test_get_relation_returns_expected_relation()
    {
        $this->resource->setRelation('relationSingle', $expected = new TestResourceTwo());

        $this->assertEquals($expected, $this->resource->getRelation('relationSingle'));
    }

    public function test_setting_value_in_relation_also_affects_the_parent()
    {
        $relation = $this->resource->getRelationSingle();

        $relation->set('property', $expected = 'new value');

        $this->assertEquals($expected, $this->resource->get('relationSingle.property'));
    }

    public function test_setting_value_in_parent_also_affects_the_relation()
    {
        $relation = $this->resource->getRelationSingle();

        $this->resource->set('relationSingle.property', $expected = 'new value');

        $this->assertEquals($expected, $relation->get('property'));
    }

    public function test_setting_value_in_relation_collection_also_affects_the_parent()
    {
        $collection = $this->resource->getRelationList();

        foreach ($collection as $resource) {
            $resource->set('property', 'new value');
        }

        $this->assertEquals(
            $collection->toArray(),
            $this->resource->getContainer()['relationList'],
        );
    }

    public function test_is_json_serializable()
    {
        $serialized = json_encode($this->resource);

        $deserialized = json_decode($serialized, true);

        $this->assertEquals($this->resource->container(), $deserialized);
    }

    public function test_given_resource_when_set_then_container_of_resource_is_set()
    {
        $resource = new class extends Resource {
            public function getContainer(): array
            {
                return $this->__container;
            }
        };

        $resource->set('relation', new Resource([
            'property' => 'value'
        ]));

        $actual = $resource->getContainer()['relation'];

        $this->assertIsArray($actual);

        $this->assertArrayHasKey('property', $actual);

        $this->assertEquals('value', $actual['property']);
    }

    public function test_given_resource_when_set_then_relation_is_set()
    {
        $resource = new Resource();

        $resource->set('relation', new Resource());

        $this->assertTrue($resource->relationIsLoaded('relation'));
    }


}