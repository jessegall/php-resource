<?php

namespace Test;

use JesseGall\Resources\ResourceCollection;
use PHPUnit\Framework\TestCase;
use Test\TestClasses\TestResource;
use Test\TestClasses\TestResourceRelation;

class ResourceTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->resource = new TestResource();
    }

    public function test_can_be_created_with_static_method()
    {
        $this->assertInstanceOf(TestResource::class, TestResource::create());
    }

    public function test_can_create_a_collection_with_static_method()
    {
        $this->assertInstanceOf(ResourceCollection::class, $collection = TestResource::collection());

        $this->assertEquals(TestResource::class, $collection->getType());
    }

    public function test_map_to_resource_creates_expected_resource()
    {
        $this->assertInstanceOf(TestResourceRelation::class, $this->resource->getRelationSingle());
    }

    public function test_map_to_resource_returns_an_array_of_expected_resources()
    {
        $actual = $this->resource->getRelationList();

        $this->assertCount(3, $actual);

        foreach ($actual as $resource) {
            $this->assertInstanceOf(TestResourceRelation::class, $resource);
        }
    }

    public function test_map_to_resource_returns_null_when_item_is_null()
    {
        $this->assertNull($this->resource->getRelationMissing());
    }

    public function test_relation_is_returned_from_loaded_relations_when_relation_is_loaded()
    {
        $resource = $this->createPartialMock(TestResource::class, ['getRelation']);

        $resource->setRelation('relationSingle', new TestResourceRelation());

        $resource->method('getRelation')->willReturn(new TestResourceRelation());

        $resource->expects($this->once())
            ->method('getRelation')
            ->with('relationSingle')
            ->willReturn(new TestResourceRelation());

        $resource->getRelationSingle();
    }

    public function test_get_relation_returns_expected_relation()
    {
        $this->resource->setRelation('relationSingle', $expected = new TestResourceRelation());

        $this->assertEquals($expected, $this->resource->getRelation('relationSingle'));
    }

}