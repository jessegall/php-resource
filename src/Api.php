<?php

namespace JesseGall\Resources;

use JesseGall\Resources\Exceptions\ApiException;

interface Api
{

    /**
     * Get the total count of the given resource
     *
     * @param string $resource
     * @param array $params
     * @return int
     * @throws ApiException
     */
    public function count(string $resource, array $params = []): int;

    /**
     * Get data for the given resource or collection of resources
     *
     * @param string $resource
     * @param int|string|null $id
     * @param array $params
     * @return array
     * @throws ApiException
     */
    public function get(string $resource, int|string $id = null, array $params = []): array;

    /**
     * Create a new resource
     *
     * @param string $resource
     * @param array $data
     * @return array
     * @throws ApiException
     */
    public function create(string $resource, array $data): array;

    /**
     * Update the given resource
     *
     * @param string $resource
     * @param int|string $id
     * @param array $data
     * @return array
     * @throws ApiException
     */
    public function update(string $resource, int|string $id, array $data = []): array;

    /**
     * Delete the given resource
     *
     * @param string $resource
     * @param int|string $id
     * @return void
     * @throws ApiException
     */
    public function delete(string $resource, int|string $id): void;

}