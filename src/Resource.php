<?php

namespace JesseGall\Resources;

use JesseGall\ContainsData\ContainsData;
use JesseGall\ContainsData\ReferenceMissingException;
use JesseGall\Events\Dispatcher;
use JesseGall\Hooks\HasHooks;

class Resource implements \JsonSerializable
{
    use ContainsData, HasHooks {
        ContainsData::set as __set;
        ContainsData::clear as __clear;
    }

    /**
     * The event dispatcher instance.
     *
     * @var Dispatcher
     */
    protected Dispatcher $dispatcher;

    /**
     * The loaded relations of the resource
     *
     * @var array<string, Resource>
     */
    protected array $relations = [];

    public function __construct(array $data = [])
    {
        $this->container($data);

        $this->dispatcher = new Dispatcher();

        $this->initialize();
    }

    /**
     * @return void
     */
    public function initialize(): void
    {
        $this->initializeHooks();

        $this->dispatch(Event::INITIALIZED);
    }

    /**
     * Creates a new resource
     *
     * @param array $data
     * @return static
     */
    public static function new(array $data = []): static
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
     * Get a new collection for the resource type
     *
     * @param array $items
     * @return ResourceCollection<static>
     */
    public static function collection(array $items = []): ResourceCollection
    {
        return ResourceCollection::new(static::class, $items);
    }

    /**
     * Get a new collection with reference for the resource type
     *
     * @param array $items
     * @return ResourceCollection<static>
     */
    public static function collectionFromReference(array &$items = []): ResourceCollection
    {
        return ResourceCollection::new(static::class, $items);
    }

    /**
     * Register a listener.
     *
     * @param Event $event
     * @param object|string|array $listeners
     * @return void
     */
    public function listen(Event $event, object|string|array $listeners): void
    {
        $this->dispatcher->listen($event->value, $listeners);
    }

    /**
     * Dispatch an event.
     *
     * @param Event $event
     * @param mixed|null $payload
     * @return void
     */
    public function dispatch(Event $event, mixed $payload = null): void
    {
        $this->dispatcher->dispatch($event->value, $payload);
    }

    /**
     * Overwrites the set method from ContainsData trait.
     * When the given value is a resource, set the container of the resource as data and load the relation
     *
     * @param string $key
     * @param mixed|null $value
     * @return Resource
     */
    public function set(string $key, mixed $value = null): static
    {
        foreach ($this->listeners['setValue'] ?? [] as $listener) {
            $value = $listener($key, $value);
        }

        if ($value instanceof Resource) {
            $this->setAsReference($key, $value->container());

            $this->setRelation($key, $value);
        } else if ($value instanceof ResourceCollection) {
            $data = [];

            foreach ($value->all() as $resource) {
                $data[] = &$resource->container();
            }

            $this->setAsReference($key, $data);

            $this->setRelation($key, $value);
        } else {
            $this->__set($key, $value);
        }

        return $this;
    }

    /**
     * Clears the data from the resource except the data from the loaded relations.
     *
     * @param array $except
     * @return void
     */
    public function clear(array $except = []): void
    {
        $this->__clear([
            ...array_keys($this->relations),
            ...$except
        ]);
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

        $relation = $asCollection
            ? $type::collectionFromReference($data)
            : $type::createFromReference($data);

        $this->setRelation($key, $relation);

        return $relation;
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