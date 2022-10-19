<?php

namespace Tests;

use JesseGall\Resources\RelationResolver;
use PHPUnit\Framework\TestCase;
use Tests\TestClasses\TestResource;
use Tests\TestClasses\TestResourceThree;
use Tests\TestClasses\TestResourceTwo;

class RelationResolverTest extends TestCase
{

    public function test_when_resolve_relations_then_correct_relations_returned()
    {
        $resolver = new RelationResolver();

        $relations = $resolver->resolveRelations(new TestResource());

        $this->assertArrayHasKey('getTestResourceTwo', $relations);
        $this->assertArrayHasKey('getTestResourceThree', $relations);

        $this->assertEquals(TestResourceTwo::class, $relations['getTestResourceTwo']);
        $this->assertEquals(TestResourceThree::class, $relations['getTestResourceThree']);
    }

}