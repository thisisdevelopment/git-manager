<?php

namespace ThisIsDevelopment\GitManager\Models;

use ThisIsDevelopment\GitManager\Exceptions\GitException;
use RuntimeException;

abstract class AbstractGitModel
{
    /**
     * @var array
     */
    protected static $properties = [];
    /**
     * @var array
     */
    protected static $updatable = [];
    /**
     * @var array
     */
    protected $data = [];

    protected function hydrate(array $data = []): void
    {
        if (!empty($data)) {
            foreach ($data as $field => $value) {
                if (in_array($field, static::$properties, true)) {
                    $this->data[$field] = $value;
                }
            }
        }
    }

    public function __set($property, $value)
    {
        throw new RuntimeException('Git model properties are immutable');
    }

    public function __get($property)
    {
        if (!in_array($property, static::$properties, true)) {
            throw new RuntimeException(sprintf(
                'Property "%s" does not exist for %s object',
                $property,
                static::class
            ));
        }

        return $this->data[$property] ?? null;
    }

    public function __isset($property)
    {
        return isset($this->data[$property]);
    }

    private static function checkAllPropertiesAllowed($properties): void
    {
        if (count(array_intersect_key(static::$updatable, $properties)) !== count($properties)) {
            throw new GitException(sprintf(
                'Invalid properties specified: (%s) allowed: (%s)',
                implode(',', array_keys($properties)),
                implode(',', array_keys(static::$updatable))
            ));
        }
    }

    public static function validateAdd(array $properties): void
    {
        static::checkAllPropertiesAllowed($properties);
        foreach (static::$updatable as $key => $required) {
            if ($required && !array_key_exists($key, $properties)) {
                throw new GitException("Missing required property: {$key}");
            }
        }
    }

    public static function validateUpdate(array $properties): void
    {
        static::checkAllPropertiesAllowed($properties);
    }
}
