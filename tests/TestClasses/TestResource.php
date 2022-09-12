<?php

namespace Test\TestClasses;

use JesseGall\Resources\Resource;

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

    public function getRelationSingle()
    {
        return $this->relation('relationSingle', TestResourceRelation::class);
    }

    public function getRelationList()
    {
        return $this->relation('relationList', TestResourceRelation::class, true);
    }

    public function getRelationMissing()
    {
        return $this->relation('relationMissing', TestResourceRelation::class);
    }

}