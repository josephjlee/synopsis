<?php

namespace TheIconic\Synopsis;

use Exception;
use TheIconic\Synopsis\Object\IteratorSynopsis;
use TheIconic\Synopsis\Resource\FileSynopsis;
use TheIconic\Synopsis\Resource\StreamSynopsis;

/**
 * the synopsis factory spawns synopsis instances
 *
 * @package TheIconic\Synopsis
 */
class Factory
{
    /**
     * @var array mappings from types to synopsis classes
     */
    protected $typeMap = [
        'null' => NullSynopsis::class,
        'boolean' => BooleanSynopsis::class,
        'string' => StringSynopsis::class,
        'double' => DoubleSynopsis::class,
        'integer' => IntegerSynopsis::class,
        'array' => ArraySynopsis::class,
        'object' => ObjectSynopsis::class,
        'resource' => ResourceSynopsis::class,
        'exception' => ExceptionSynopsis::class,
    ];

    /**
     * @var array mappings from value classes to synopsis classes
     */
    protected $objectMap = [
        'Iterator' => IteratorSynopsis::class,
        'IteratorAggregate' => IteratorSynopsis::class,
        'ArrayAccess' => IteratorSynopsis::class,
    ];

    /**
     * @var array mappings from resource types to synopsis classes
     */
    protected $resourceMap = [
        'bzip2' => FileSynopsis::class,
        'cpdf' => FileSynopsis::class,
        'fdf' => FileSynopsis::class,
        'zlib' => FileSynopsis::class,
        'stream' => StreamSynopsis::class,
    ];

    /**
     * creates the fitting synopsis instance for a value
     *
     * @param $value
     * @param $depth
     * @return mixed
     */
    public function synopsize($value, $depth = 3)
    {
        $depth--;
        if ($depth <= 0) {
            $depth = false;
        }

        $className = $this->getClassName($value);

        /** @var AbstractSynopsis $synopsis */
        $synopsis = new $className();
        $synopsis->setFactory($this);
        $synopsis->process($value, $depth);

        return $synopsis;
    }

    /**
     * get the synopsis classname to use for processing value
     *
     * @param $value
     * @return string
     */
    protected function getClassName($value)
    {
        $type = $this->detectType($value);

        if ($type === 'object') {
            return $this->getClassNameForObject($value);
        }

        if ($type === 'resource') {
            return $this->getClassNameForResource($value);
        }

        return $this->getClassNameForType($type);
    }

    /**
     * detect the primitive type of value
     *
     * @param $value
     * @return string
     */
    protected function detectType($value)
    {
        if ($value === null) {
            return 'null';
        }

        if ($value instanceof Exception) {
            return 'exception';
        }

        return gettype($value);
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getClassNameForType(string $type)
    {
        if (isset($this->typeMap[$type])) {
            $className = $this->typeMap[$type];

            if (class_exists($className)) {
                return $className;
            }
        }

        return StandardSynopsis::class;
    }

    /**
     * get the fitting synopsis class name for the given object
     *
     * @param $value
     * @return string
     */
    protected function getClassNameForObject($value): string
    {
        foreach ($this->objectMap as $type => $className) {
            if (!is_a($value, $type)) {
                continue;
            }

            if (class_exists($className)) {
                return $className;
            }
        }

        return $this->typeMap['object'];
    }

    /**
     * get the fitting synopsis class name for the given resource
     *
     * @param $value
     * @return string
     */
    protected function getClassNameForResource($value): string
    {
        $type = get_resource_type($value);

        if (isset($this->resourceMap[$type])) {
            $className = $this->resourceMap[$type];

            if (class_exists($className)) {
                return $className;
            }
        }

        return $this->typeMap['resource'];
    }

    /**
     * register a Synopsis class for resource type
     *
     * @param $type
     * @param $className
     * @return $this
     */
    public function addResourceType($type, $className)
    {
        $this->resourceMap[$type] = $className;

        return $this;
    }

    /**
     * register a Synopsis class for an object type
     *
     * @param $type
     * @param $className
     * @return $this
     */
    public function addObjectType($type, $className)
    {
        $this->objectMap[$type] = $className;

        return $this;
    }

    /**
     * register a Synopsis class for a type
     *
     * @param $type
     * @param $className
     * @return $this
     */
    public function addType($type, $className)
    {
        $this->typeMap[$type] = $className;

        return $this;
    }
}
