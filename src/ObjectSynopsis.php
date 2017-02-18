<?php

namespace TheIconic\Synopsis;

use ReflectionClass;
use ReflectionProperty;

/**
 * represents an object
 *
 * @package TheIconic\Synopsis
 */
class ObjectSynopsis extends AbstractSynopsis
{
    /**
     * @var string the namespace
     */
    protected $namespace;

    /**
     * @see parent::process()
     * @param $value
     * @param $depth
     */
    public function process($value, $depth)
    {
        parent::process($value, $depth);

        $reflector = new ReflectionClass($value);

        $properties = $reflector->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $this->addChild($this->getFactory()->synopsize($property->getValue($value), $depth), $property->getName());
        }

        if (empty($this->length)) {
            $this->length = count($this->children);
        }

        $this->type = $reflector->getName();

        foreach (['__toSynopsisValue', '__toString', 'getId', 'getName'] as $method) {
            if (method_exists($value, $method) && is_callable([$value, $method])) {
                $this->value = (string) call_user_func([$value, $method]);
            }
        }
    }

    /**
     * get the namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }
}
