<?php

namespace Tests\TestClasses;

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

    public function getRelationSingle(): TestResourceTwo
    {
        return $this->relation('relationSingle', TestResourceTwo::class);
    }

    public function getRelationList(): ResourceCollection
    {
        return $this->relation('relationList', TestResourceTwo::class, true);
    }

    public function getRelationMissing(): ?TestResourceTwo
    {
        return $this->relation('relationMissing', TestResourceTwo::class);
    }

    public function getTestResourceTwo(): ?TestResourceTwo
    {
        return $this->relation('resourceTwo', TestResourceTwo::class);
    }

    public function getTestResourceThree(): ?TestResourceThree
    {
        return $this->relation('resourceTwo', TestResourceThree::class);
    }

    public function getContainer(): array
    {
        return $this->__container;
    }

}