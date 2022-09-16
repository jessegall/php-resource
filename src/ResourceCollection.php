<?php

namespace JesseGall\Resources;

use InvalidArgumentException;

/**
 * @template T of \JesseGall\Resources\Resource
 */
class ResourceCollection implements \Iterator, \ArrayAccess
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
        $this->resources = $resources;
    }

    /**
     * Creates a new resource collection
     *
     * @param class-string<\JesseGall\Resources\Resource> $type
     * @param array $items
     * @return static
     */
    public static function create(string $type, array $items): static
    {
        return new static($type, array_map(fn($data) => $type::create($data), $items));
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
     * @return array
     */
    public function all(): array
    {
        return $this->resources;
    }

    # --- Getters and setters ---

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

}