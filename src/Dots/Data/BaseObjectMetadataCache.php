<?php
/**
 * Description of BaseObjectMetadataCache.php.
 *
 * Keeps the class metadata of BaseObject subclasses (property defaults and
 * which properties hold nested objects or enums) once per class and PHP process.
 * Class definitions do not change at runtime, so the cache never goes stale,
 * also under Octane and Lambda where the process serves many requests.
 *
 * The cache lives in its own class on purpose: a static property declared in
 * BaseObject itself would be returned by get_class_vars() and would become a
 * property of every DTO.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dots\Data;

final class BaseObjectMetadataCache
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private static array $propertiesValuesByClass = [];

    /**
     * @var array<string, list<string>>
     */
    private static array $propertyNamesByClass = [];

    /**
     * @var array<string, array{fromArray: array<string, string>, enums: array<string, string>}>
     */
    private static array $classMetadataByClass = [];

    public static function hasPropertiesValues(string $className): bool
    {
        return isset(self::$propertiesValuesByClass[$className]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function getPropertiesValues(string $className): array
    {
        return self::$propertiesValuesByClass[$className];
    }

    /**
     * @param array<string, mixed> $propertiesValues
     */
    public static function putPropertiesValues(string $className, array $propertiesValues): void
    {
        self::$propertiesValuesByClass[$className] = $propertiesValues;
    }

    /**
     * One lookup instead of has + get: this runs for every created DTO.
     *
     * @return array<string, mixed>|null
     */
    public static function findPropertiesValues(string $className): ?array
    {
        return self::$propertiesValuesByClass[$className] ?? null;
    }

    /**
     * @return list<string>|null
     */
    public static function findPropertyNames(string $className): ?array
    {
        return self::$propertyNamesByClass[$className] ?? null;
    }

    /**
     * @param list<string> $propertyNames
     */
    public static function putPropertyNames(string $className, array $propertyNames): void
    {
        self::$propertyNamesByClass[$className] = $propertyNames;
    }

    /**
     * @return array{fromArray: array<string, string>, enums: array<string, string>}|null
     */
    public static function findClassMetadata(string $className): ?array
    {
        return self::$classMetadataByClass[$className] ?? null;
    }

    public static function hasClassMetadata(string $className): bool
    {
        return isset(self::$classMetadataByClass[$className]);
    }

    /**
     * @return array{fromArray: array<string, string>, enums: array<string, string>}
     */
    public static function getClassMetadata(string $className): array
    {
        return self::$classMetadataByClass[$className];
    }

    /**
     * @param array{fromArray: array<string, string>, enums: array<string, string>} $classMetadata
     */
    public static function putClassMetadata(string $className, array $classMetadata): void
    {
        self::$classMetadataByClass[$className] = $classMetadata;
    }

    public static function flush(): void
    {
        self::$propertiesValuesByClass = [];
        self::$propertyNamesByClass = [];
        self::$classMetadataByClass = [];
    }
}
