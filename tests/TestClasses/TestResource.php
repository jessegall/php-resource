<?php

namespace Test\TestClasses;

use JesseGall\Resources\Resource;
use JesseGall\Resources\ResourceCollection;

class TestResource extends Resource
{

    public function __construct()
    {
        parent::__construct([
            'property_one' => 'one',
            'property_two' => 'two',
            'property_three' => 'three',
            'relationSingle' => [
                'property' => 'value'
            ],
            'relationList' => [
                ['property' => 'value'],
                ['property' => 'value'],
                ['property' => 'value'],
            ],
            'relationMissing' => null,
        ]);
    }

    public function getRelationSingle(): TestResourceRelation
    {
        return $this->relation('relationSingle', TestResourceRelation::class);
    }

    public function getRelationList(): ResourceCollection
    {
        return $this->relation('relationList', TestResourceRelation::class, true);
    }

    public function getRelationMissing()
    {
        return $this->relation('relationMissing', TestResourceRelation::class);
    }

    public function getContainer(): array
    {
        return $this->__container;
    }

}