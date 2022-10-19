<?php

namespace Tests;

use JesseGall\Resources\RelationResolver;
use PHPUnit\Framework\TestCase;
use Tests\TestClasses\TestResource;
use Tests\TestClasses\TestResourceThree;
use Tests\TestClasses\TestResourceTwo;

class RelationResolverTest extends TestCase
{

    public function test_when_resolve_relation_methods_then_correct_methods_returned()
    {
        $resolver = new RelationResolver();

        $methods = $resolver->resolveRelationMethods(new TestResource());

        $this->assertArrayHasKey('getRelationList', $methods);
        $this->assertArrayHasKey('getTestResourceTwo', $methods);
        $this->assertArrayHasKey('getTestResourceThree', $methods);

        $this->assertEquals(TestResourceTwo::class, $methods['getRelationList']);
        $this->assertEquals(TestResourceTwo::class, $methods['getTestResourceTwo']);
        $this->assertEquals(TestResourceThree::class, $methods['getTestResourceThree']);
    }

    public function test_when_resolve_relations_then_correct_relations_returned()
    {
        $resolver = new RelationResolver();

        $relations = $resolver->resolveRelations(new TestResource());

        $this->assertCount(2, $relations);
        $this->assertContains(TestResourceTwo::class, $relations);
        $this->assertContains(TestResourceThree::class, $relations);
    }

}