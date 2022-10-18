<?php

namespace JesseGall\Resources;

use Closure;
use InvalidArgumentException;

/**
 * @template T of \JesseGall\Resources\Resource
 */
class ResourceCollection implements \Iterator, \ArrayAccess, \JsonSerializable, \Countable
{

    /**
     * The current index of the collection
     *
     * @var int
     */
    protected int $index = 0;

    /**
     * @var class-string<T>
     */
    private string $type;

    /**
     * @var T[]
     */
    protected array $resources;

    /**
     * @param class-string<T> $type
     * @param T[] $resources
     */
    public function __construct(string $type, array $resources = [])
    {
        foreach ($resources as $resource) {
            if (! ($resource instanceof $type)) {
                throw new InvalidArgumentException("All resources should be of type $type");
            }
        }

        $this->type = $type;
        $this->resources = &$resources;
    }

    /**
     * Creates a new resource collection
     *
     * @param class-string<\JesseGall\Resources\Resource> $type
     * @param array $items
     * @return static
     */
    public static function new(string $type, array &$items): static
    {
        $resources = [];

        foreach ($items as &$item) {
            $resources[] = $type::createFromReference($item);
        }

        return new static($type, $resources);
    }

    /**
     * @return T
     */
    public function current(): Resource
    {
        return $this->resources[$this->index];
    }

    /**
     * @return void
     */
    public function next(): void
    {
        $this->index++;
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->index;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->resources[$this->index]);
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->resources[$offset]);
    }

    /**
     * @param mixed $offset
     * @return T
     */
    public function offsetGet(mixed $offset): Resource
    {
        return $this->resources[$offset];
    }

    /**
     * @param mixed $offset
     * @param Resource $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (! ($value instanceof $this->type)) {
            throw new InvalidArgumentException("Value should be an instance of $this->type");
        }

        $this->resources[$offset] = $value;
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->resources[$offset]);
    }

    /**
     * Returns the first resource in the collection
     *
     * @return T
     */
    public function first(): Resource
    {
        return $this->resources[0];
    }

    /**
     * Returns all the resources in an array
     *
     * @return T[]
     */
    public function all(): array
    {
        return $this->resources;
    }

    /**
     * Map each resource to the result of the callback
     *
     * @param Closure $callback
     * @return array
     */
    public function map(Closure $callback): array
    {
        return array_map($callback, $this->resources);
    }

    /**
     * Filter the collection.
     *
     * @param Closure $closure
     * @return $this
     */
    public function filter(Closure $closure): static
    {
        $resources = array_filter($this->resources, $closure);

        return new static($this->type, $resources);
    }

    /**
     * Sort the collection and get a new sorted collection instance
     *
     * @param string|Closure $property
     * @param string $direction
     * @return $this
     */
    public function sort(string|Closure $property, string $direction = 'asc'): static
    {
        $resources = $this->resources;

        if (is_callable($property)) {
            usort($resources, $property);
        } else {
            usort($resources, function (Resource $a, Resource $b) use ($property, $direction, $resources) {
                $a = $a->get($property);
                $b = $b->get($property);
                return match ($direction) {
                    'asc' => strnatcmp($a, $b),
                    'desc' => strnatcmp($b, $a),
                };
            });
        }

        return new static($this->type, $resources);
    }

    /**
     * Returns the array representation of the resource
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->map(fn(Resource $resource) => $resource->toArray());
    }

    /**
     * Get the count of resources in the collection
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->resources);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

}