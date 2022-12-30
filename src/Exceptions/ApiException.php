<?php

namespace JesseGall\Resources\Exceptions;

use Exception;

class ApiException extends Exception
{

    /**
     * The resource that caused the exception
     *
     * @var class-string<\JesseGall\Resources\Resource>
     */
    private string $resourceType;

    /**
     * Create a new exception instance
     *
     * @param string $resourceType
     * @param string $message
     * @param int $code
     */
    public function __construct(string $resourceType, string $message = '', int $code = 0)
    {
        parent::__construct($message, $code);

        $this->resourceType = $resourceType;
    }

    /**
     * Get the resource type
     *
     * @return class-string<\JesseGall\Resources\Resource>
     */
    public function getResourceType(): string
    {
        return $this->resourceType;
    }

}