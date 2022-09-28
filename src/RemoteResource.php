<?php

namespace JesseGall\Resources;

interface RemoteResource
{

    /**
     * Return a collection of all remote resources
     *
     * @param array $params
     * @return ResourceCollection<static>
     */
    public static function all(array $params = []): ResourceCollection;

    /**
     * Find a remote resource by key
     *
     * @param string|int $key
     * @return static
     */
    public static function find(string|int $key): static;

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
     * Sync the local resource with the remote resource.
     * Return true when successfully synced.
     *
     * @return bool
     */
    public function sync(): bool;

    /**
     * Delete the remote resource.
     * Return true when successfully deleted.
     *
     * @return bool
     */
    public function delete(): bool;

}