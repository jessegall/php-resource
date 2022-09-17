<?php

namespace JesseGall\Resources;

use JesseGall\ContainsData\ContainsData;
use JesseGall\ContainsData\ReferenceMissingException;

class Resource implements \JsonSerializable
{
    use ContainsData {
        ContainsData::set as __set;
    }

    /**
     * @var array<string, Resource>
     */
    protected array $relations = [];

    public function __construct(array $data = [])
    {
        $this->container($data);
    }

    /**
     * Creates a new resource
     *
     * @param array $data
     * @return static
     */
    public static function create(array $data = []): static
    {
        return new static($data);
    }

    /**
     * Creates a new resource with reference to a container
     *
     * @param array $data
     * @return static
     */
    public static function createFromReference(array &$data = []): static
    {
        $resource = new static;

        $resource->container($data);

        return $resource;
    }

    /**
     * Returns a new collection for the resource type
     *
     * @param array $items
     * @return ResourceCollection<static>
     */
    public static function collection(array &$items = []): ResourceCollection
    {
        return ResourceCollection::create(static::class, $items);
    }

    /**
     * Overwrites the set method from ContainsData trait to make it fluent
     *
     * @param string $key
     * @param mixed|null $value
     * @return Resource
     */
    public function set(string $key, mixed $value = null): static
    {
        $this->__set($key, $value);

        return $this;
    }

    /**
     * Map the given item(s) to the given resource type
     *
     * @template T of \JesseGall\Resources\Resource
     * @param string $key
     * @param class-string<\JesseGall\Resources\Resource> $type
     * @param bool $asCollection
     * @return T|ResourceCollection<T>|null
     * @throws ReferenceMissingException
     */
    public function relation(string $key, string $type, bool $asCollection = false): Resource|ResourceCollection|null
    {
        if ($this->relationIsLoaded($key)) {
            return $this->getRelation($key);
        }

        $data = &$this->getAsReference($key);

        if (is_null($data)) {
            return null;
        }

        $relation = $asCollection ? $type::collection($data) : $type::createFromReference($data);

        $this->setRelation($key, $relation);

        return $relation;
    }

    /**
     * Returns a loaded relation
     *
     * @param string $key
     * @return Resource|ResourceCollection
     */
    public function getRelation(string $key): Resource|ResourceCollection
    {
        return $this->relations[$key];
    }

    /**
     * Sets the relation
     *
     * @param string $key
     * @param Resource|ResourceCollection $relation
     * @return void
     */
    public function setRelation(string $key, Resource|ResourceCollection $relation): void
    {
        $this->relations[$key] = $relation;
    }

    /**
     * Check if the given key exists in teh relations array
     *
     * @param string $key
     * @return bool
     */
    public function relationIsLoaded(string $key): bool
    {
        return array_key_exists($key, $this->relations);
    }

    /**
     * Returns the array representation of the resource
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->container();
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}