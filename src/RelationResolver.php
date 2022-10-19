<?php

namespace JesseGall\Resources;

use ReflectionException;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;

class RelationResolver
{

    /**
     * Get the methods that return a relation
     *
     * Returns an array where the keys are the method, and the value the relation resource type.
     *
     * @param Resource $resource
     * @return array
     */
    public function resolveRelationMethods(Resource $resource): array
    {
        $resolved = [];

        $reflection = new \ReflectionClass($resource);

        $methods = $reflection->getMethods();

        foreach ($methods as $method) {
            $resourceType = $this->resolveResourceTypeFromMethod($resource, $method);

            if (is_null($resourceType)) {
                continue;
            }

            $resolved[$method->getName()] = $resourceType;
        }

        return $resolved;
    }

    public function resolveRelations(Resource $resource): array
    {
        $methods = $this->resolveRelationMethods($resource);

        return array_values(array_unique($methods));
    }

    /**
     * Get the resource type from a method
     *
     * In case the method returns an instance of resource collection, the resource type of the collection is used.
     *
     * @param Resource $resource
     * @param ReflectionMethod $method
     * @return string|null
     * @throws ReflectionException
     */
    protected function resolveResourceTypeFromMethod(Resource $resource, ReflectionMethod $method): ?string
    {
        $return = $method->getReturnType();

        if (is_null($return)) {
            return null;
        }

        if ($return instanceof ReflectionIntersectionType || $return instanceof ReflectionUnionType) {
            $types = $return->getTypes();
        } else {
            $types = [$return];
        }

        foreach ($types as $type) {
            if (! ($type instanceof ReflectionNamedType)) {
                continue;
            }

            if (count($method->getParameters()) > 0) {
                continue;
            }

            if ($this->isResourceType($type)) {
                return $type->getName();
            } else if ($this->isResourceCollectionType($type)) {
                $collection = $method->invoke($resource);

                return $collection->getType();
            }
        }

        return null;
    }

    /**
     * Check if the type is a resource
     *
     * @param ReflectionNamedType $type
     * @return bool
     */
    protected function isResourceType(ReflectionNamedType $type): bool
    {
        $name = $type->getName();

        return is_subclass_of($name, Resource::class) || $name === Resource::class;
    }

    /**
     * Check if the type is a resource collection
     *
     * @param ReflectionNamedType $type
     * @return bool
     */
    protected function isResourceCollectionType(ReflectionNamedType $type): bool
    {
        $name = $type->getName();

        return is_subclass_of($name, ResourceCollection::class) || $name === ResourceCollection::class;
    }

}