<?php

namespace Sitnikovik\ArrayToObjectSerializer;

use InvalidArgumentException;
use ReflectionException;
use ReflectionProperty;

/**
 * Class to serialize array to object
 */
class ArrayToObject
{
    /**
     * Serializes array to object
     *
     * @param array $array
     * @param string $className
     * @return object
     * @throws ReflectionException
     */
    public static function serialize(array $array, string $className): object
    {
        $object = new $className();

        foreach ($array as $key => $value) {
            if (!property_exists($className, $key)) {
                continue;
            }

            if (!is_object($value)) {
                // Parsing PHPDoc comment for property
                $phpDocProps = self::parseDocComment(self::getDocComment($className, $key));

                if (is_array($value) && !empty($phpDocProps) && !empty($phpDocProps["ToObject"])) {
                    // if property is to be converted to object

                    $toObject = new ToObject($phpDocProps["ToObject"][0]); // Get class to convert in
                    if (!self::isArrayAssoc($value)) {
                        // If it is collection of objects
                        $value = array_map(static function ($item) use ($toObject) {
                            return static::serialize($item, $toObject->getClass());
                        }, $value);
                    } else {
                        // If it is single object
                        $value = static::serialize($value, $toObject->getClass());
                    }
                } elseif (!empty($phpDocProps) && !empty($phpDocProps["var"])) {
                    // Check property allowed types if specified
                    $allowTypes = $phpDocProps["var"];
                    if (!is_array($allowTypes)) {
                        $allowTypes = [$allowTypes];
                    }
                    if (in_array('int', $allowTypes, true)) {
                        // fixes bug with int type
                        $allowTypes[] = 'integer';
                    }
                    if (in_array('float', $allowTypes, true)) {
                        // fixes bug with double type
                        $allowTypes[] = 'integer';
                    }

                    // Checking
                    $type = gettype($value);
                    if (!in_array($type, $allowTypes, true)) {
                        throw new InvalidArgumentException(sprintf(
                            'Invalid type of value for property "%s". Allowed types: %s. Given: %s',
                            $key, implode(', ', $allowTypes), $type
                        ));
                    }
                }
            }

            // Set value to object
            $object->$key = $value;
        }

        return $object;
    }

    /**
     * Parses PHPDoc comment
     *
     * @param string $docComment
     * @return array
     */
    protected static function parseDocComment(string $docComment): array
    {
        $result = [];

        $docComment = str_replace(['/**', '*/', '* ', '*'], '', $docComment);
        $docComment = trim($docComment);
        $docComments = explode("\n", $docComment);

        foreach ($docComments as $item) {
            $item = trim($item);
            if (strpos($item, '@') === 0) {
                $posOfFirstScalpel = strpos($item, '(');
                if ($posOfFirstScalpel !== false) {
                    // Parse class annotations...

                    $key = substr($item, 1, $posOfFirstScalpel - 1); // Annotation name

                    // Parsing annotation value
                    $posOfLastScalpel = strrpos($item, ')');
                    $value = substr($item, $posOfFirstScalpel + 1, $posOfLastScalpel - $posOfFirstScalpel - 1);
                    $value = explode(',', $value);
                    $value = array_map(static function ($item) {
                        return trim($item);
                    }, $value);

                    $result[$key] = $value;
                    continue;
                }

                $posOfFirstSpace = strpos($item, ' ');
                if ($posOfFirstSpace !== false) {
                    // Parse common annotations with scalpels like @var, @param, @return...

                    $key = substr($item, 1, $posOfFirstSpace - 1);
                    $value = trim(substr($item, $posOfFirstSpace + 1));
                    if (strpos($value, '|') !== false) {
                        // If value is a list of values
                        $value = explode('|', $value);
                    }

                    $result[$key] = $value;
                }
            }
        }


        return $result;
    }

    /**
     * Returns PHPDoc comment for property
     *
     * @throws ReflectionException
     */
    private static function getDocComment(string $className, string $propertyName): string
    {
        return (new ReflectionProperty($className, $propertyName))->getDocComment();
    }

    /**
     * Checks if array is associative
     *
     * @param array $array
     * @return bool
     */
    private static function isArrayAssoc(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}