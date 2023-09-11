<?php

namespace Sitnikovik\ArrayToObjectSerializer;

use BadMethodCallException;

/**
 * Annotation class for @ToObject().
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class ToObject
{
    /**
     * @var string
     */
    private $class;

    /**
     * @param string $class
     */
    public function __construct(string $class)
    {
        if (!class_exists($class)) {
            throw new BadMethodCallException("Class $class does not exist");
        }

        $this->class = $class;
    }

    /**
     * Returns class name
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }
}