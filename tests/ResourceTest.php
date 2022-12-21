<?php

namespace Tests;

use JesseGall\Resources\Resource;
use JesseGall\Resources\ResourceCollection;
use PHPUnit\Framework\TestCase;
use Tests\TestClasses\TestResource;
use Tests\TestClasses\TestResourceTwo;

class ResourceTest extends TestCase
{

    public function test_can_set_value()
    {
        $resource = new TestResource();

        $resource->set('nested.property', 'nested value');

        $this->assertEquals(
            'nested value',
            $resource->getContainer()['nested']['property']
        );
    }

    public function test_can_be_created_with_static_method()
    {
        $this->assertInstanceOf(TestResource::class, TestResource::new());
    }

    public function test_can_create_a_collection_with_static_method()
    {
        $this->assertInstanceOf(ResourceCollection::class, $collection = TestResource::collectionFromReference());

        $this->assertEquals(TestResource::class, $collection->getType());
    }

    public function test_map_to_resource_creates_expected_resource()
    {
        $resource = new TestResource();

        $this->assertInstanceOf(TestResourceTwo::class, $resource->getRelationSingle());
    }

    public function test_map_to_resource_returns_a_collection_of_expected_resources()
    {
        $resource = new TestResource();

        $actual = $resource->getRelationList();

        $this->assertCount(3, $actual);

        foreach ($actual as $resource) {
            $this->assertInstanceOf(TestResourceTwo::class, $resource, true);
        }
    }

    public function test_map_to_resource_returns_null_when_item_is_null()
    {
        $resource = new TestResource();

        $this->assertNull($resource->getRelationMissing());
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
        $resource = new TestResource();

        $resource->setRelation('relationSingle', $expected = new TestResourceTwo());

        $this->assertEquals($expected, $resource->getRelation('relationSingle'));
    }

    public function test_setting_value_in_relation_also_affects_the_parent()
    {
        $resource = new TestResource();

        $relation = $resource->getRelationSingle();

        $relation->set('property', $expected = 'new value');

        $this->assertEquals($expected, $resource->get('relationSingle.property'));
    }

    public function test_setting_value_in_parent_also_affects_the_relation()
    {
        $resource = new TestResource();

        $relation = $resource->getRelationSingle();

        $resource->set('relationSingle.property', $expected = 'new value');

        $this->assertEquals($expected, $relation->get('property'));
    }

    public function test_setting_value_in_relation_collection_also_affects_the_parent()
    {
        $resource = new TestResource();

        $collection = $resource->getRelationList();

        foreach ($collection as $item) {
            $item->set('property', 'new value');
        }

        $this->assertEquals(
            $collection->toArray(),
            $resource->getContainer()['relationList'],
        );
    }

    public function test_is_json_serializable()
    {
        $resource = new TestResource();

        $serialized = json_encode($resource);

        $deserialized = json_decode($serialized, true);

        $this->assertEquals($resource->container(), $deserialized);
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

    public function test_given_resource_collection_when_set_then_containers_of_resources_are_set()
    {
        $resource = new class extends Resource {
            public function getContainer(): array
            {
                return $this->__container;
            }
        };

        $collection = new ResourceCollection(
            resources: array_map(fn(array $data) => new Resource($data), [
                ['property' => 'value'],
                ['property' => 'value'],
                ['property' => 'value'],
            ])
        );

        $resource->set('relation', $collection);

        $actual = $resource->getContainer()['relation'];

        $this->assertIsArray($actual);

        $this->assertCount(3, $actual);

        $this->assertEquals([
            ['property' => 'value'],
            ['property' => 'value'],
            ['property' => 'value'],
        ], $actual);
    }

    public function test__When_clear__Then_resource_cleared()
    {
        $resource = new TestResource();

        $resource->clear();

        $this->assertEmpty($resource->container());
    }

    public function test__When_clear__Then_data_from_loaded_relations_not_cleared()
    {
        $resource = new TestResource();

        // Initialize the relation
        $resource->getRelationSingle();

        $resource->clear();

        $this->assertTrue($resource->has('relationSingle'));
    }

}