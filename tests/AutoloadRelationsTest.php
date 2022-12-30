<?php

namespace Tests;

use JesseGall\Resources\Concerns\AutoloadRelations;
use JesseGall\Resources\Resource;
use PHPUnit\Framework\TestCase;

class AutoloadRelationsTest extends TestCase
{

    public function test__Given_relation_data_present__When_new_instance__Then_relations_loaded()
    {
        $resource = new class extends Resource {
            use AutoloadRelations;

            public function __construct()
            {
                $this->autoloadRelations = [
                    'someRelation' => Resource::class
                ];

                parent::__construct([
                    'someRelation' => [
                        'id' => 1,
                        'name' => 'Some relation'
                    ]
                ]);
            }

            public function someRelation(): Resource
            {
                return $this->relation('someRelation', Resource::class);
            }
        };

        $this->assertTrue($resource->relationIsLoaded('someRelation'));
    }

}