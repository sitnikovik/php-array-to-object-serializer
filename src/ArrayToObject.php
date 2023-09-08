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
                $phpDocProps = self::parseDocComment(self::getDocComment($className, $key));
                if (is_array($value) && !empty($phpDocProps) && !empty($phpDocProps["ToObject"])) {
                    $toObject = new ToObject($phpDocProps["ToObject"][0]);
                    $value = static::serialize($value, $toObject->getClass());
                } elseif (!empty($phpDocProps) && !empty($phpDocProps["var"])) {
                    $allowTypes = $phpDocProps["var"];
                    if (!is_array($allowTypes)) {
                        $allowTypes = [$allowTypes];
                    }
                    $type = gettype($value);
                    if (!in_array($type, $allowTypes, true)) {
                        throw new InvalidArgumentException(sprintf(
                            'Invalid type of value for property "%s". Allowed types: %s. Given: %s',
                            $key, implode(', ', $allowTypes), $type
                        ));
                    }
                }
            }

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
        $docComment = explode("\n", $docComment);

        foreach ($docComment as $item) {
            $item = trim($item);
            if (strpos($item, '@') === 0) {
                $posOfFirstScalpel = strpos($item, '(');
                if ($posOfFirstScalpel !== false) {
                    $key = substr($item, 1, $posOfFirstScalpel - 1);
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
                    $key = substr($item, 1, $posOfFirstSpace - 1);
                    $value = trim(substr($item, $posOfFirstSpace + 1));

                    if (strpos($value, '|') !== false) {
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
}