<?php

namespace JesseGall\Resources\Concerns;

use JesseGall\Resources\Api;
use JesseGall\Resources\Exceptions\ApiException;
use JesseGall\Resources\RemoteResource;
use JesseGall\Resources\Resource;
use JesseGall\Resources\ResourceCollection;

/**
 * @implements RemoteResource
 * @mixin Resource
 */
trait IsRemote
{

    /**
     * Indicates if the resource exists on the remote server
     *
     * @var bool
     */
    protected bool $exists = false;

    /**
     * Returns the count of all remote resources
     *
     * @param array $params
     * @return int
     */
    public static function count(array $params = []): int
    {
        try {
            return static::api()->count(static::class, $params);
        } catch (ApiException) {
            return 0;
        }
    }

    /**
     * Return a collection of all remote resources
     *
     * @param array $params
     * @return ResourceCollection<static>
     */
    public static function all(array $params = []): ResourceCollection
    {
        try {
            $data = static::api()->get(static::class, null, $params) ?? [];
        } catch (ApiException) {
            $data = [];
        }

        if (! array_is_list($data)) {
            $data = [$data];
        }

        $collection = static::collection($data);

        foreach ($collection as $resource) {
            $resource->setExists(true);
        }

        return $collection;
    }

    /**
     * Find a remote resource by key
     *
     * @param int|string $id
     * @return static|null
     */
    public static function find(int|string $id): ?static
    {
        try {
            $data = static::api()->get(static::class, $id);
        } catch (ApiException) {
            return null;
        }

        $resource = static::new($data);

        $resource->setExists(true);

        return $resource;
    }

    /**
     * Create a remote resource
     *
     * @param array $data
     * @return static|null
     */
    public static function create(array $data = []): ?static
    {
        try {
            $data = static::api()->create(static::class, $data);
        } catch (ApiException) {
            return null;
        }

        $resource = static::new($data);

        $resource->setExists(true);

        return $resource;
    }

    /**
     * Get the unique id for the resource
     *
     * @return string|int|null
     */
    public function getId(): string|int|null
    {
        return $this->get('id');
    }

    /**
     * Fill the resource with remote data.
     * Return true when successfully hydrated.
     *
     * @return bool
     */
    public function hydrate(): bool
    {
        if (! $this->getId()) {
            return false;
        }

        try {
            $data = static::api()->get(static::class, $this->getId());
        } catch (ApiException) {
            return false;
        }

        $this->merge($data);

        $this->setExists(true);

        return true;
    }

    /**
     * Loads the latest data from the remote source.
     *
     * Warning: this will overwrite any local changes
     *
     * @return bool
     */
    public function refresh(): bool
    {
        if (! $this->exists()) {
            return false;
        }

        try {
            $data = static::api()->get(static::class, $this->getId());
        } catch (ApiException) {
            return false;
        }

        $this->clear(['id']);

        $this->merge($data);

        $this->setExists(true);

        return true;
    }

    /**
     * Save the local changes to the remote source.
     * Return true when successfully saved.
     *
     * @return bool
     */
    public function save(): bool
    {
        try {
            if ($this->exists()) {
                $data = static::api()->update(static::class, $this->getId(), $this->container());
            } else {
                $data = static::api()->create(static::class, $this->container());
            }
        } catch (ApiException) {
            return false;
        }

        $this->merge($data);

        $this->setExists(true);

        return true;
    }

    /**
     * Delete the remote resource.
     * Return true when successfully deleted.
     *
     * @return bool
     */
    public function delete(): bool
    {
        if (! $this->exists()) {
            return false;
        }

        try {
            static::api()->delete(static::class, $this->getId());
        } catch (ApiException) {
            return false;
        }

        $this->setExists(false);

        return true;
    }

    /**
     * Returns true when the resource exists on the remote server
     *
     * @return bool
     */
    public function exists(): bool
    {
        return $this->exists;
    }

    /**
     * Set the exists flag
     *
     * @param bool $exists
     * @return $this
     */
    public function setExists(bool $exists): static
    {
        $this->exists = $exists;

        return $this;
    }

    /**
     * Returns the API.
     *
     * @return Api
     */
    protected abstract static function api(): Api;

}