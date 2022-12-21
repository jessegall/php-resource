<?php

namespace JesseGall\Resources;

/**
 * @template T of \JesseGall\Resources\Resource
 */
interface RemoteResource
{

    /**
     * Return a collection of all remote resources
     *
     * @param array $params
     * @return ResourceCollection<T>
     */
    public static function all(array $params = []): ResourceCollection;

    /**
     * Find a remote resource by key
     *
     * @param string|int $key
     * @return static|null
     */
    public static function find(string|int $key): ?static;

    /**
     * Create a remote resource
     *
     * @param array $data
     * @return static
     */
    public static function create(array $data = []): static;

    /**
     * Fill the resource with remote data.
     * Return true when successfully hydrated.
     *
     * @return bool
     */
    public function hydrate(): bool;

    /**
     * Loads the latest data from the remote source.
     *
     * Warning: this will overwrite any local changes
     *
     * @return bool
     */
    public function refresh(): bool;

    /**
     * Save the local changes to the remote source.
     * Return true when successfully saved.
     *
     * @return bool
     */
    public function save(): bool;

    /**
     * Delete the remote resource.
     * Return true when successfully deleted.
     *
     * @return bool
     */
    public function delete(): bool;

}