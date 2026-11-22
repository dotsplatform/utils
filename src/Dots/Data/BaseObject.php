<?php
/**
 * Description of BaseObject.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Dots\Data;

use BackedEnum;
use Illuminate\Contracts\Support\Arrayable;
use ReflectionClass;
use ReflectionNamedType;

abstract class BaseObject implements Arrayable, FromArrayable
{
    private function __construct(
        array $data,
    ) {
        $this->assertConstructDataIsValid($data);
        $propertiesValues = static::getPropertiesValues();
        $classMetadata = self::getClassMetadata(static::class);
        $fromArrayableProperties = $classMetadata['fromArray'];
        $enumProperties = $classMetadata['enums'];

        foreach ($propertiesValues as $property => $defaultValue) {
            // A missing key and a null value both take the declared default.
            if (!isset($data[$property])) {
                $this->$property = $defaultValue;
                continue;
            }
            if (isset($fromArrayableProperties[$property])) {
                $this->$property = self::buildFromArrayableValue($fromArrayableProperties[$property], $data[$property]);
                continue;
            }
            if (isset($enumProperties[$property])) {
                $this->$property = self::buildEnumValue($enumProperties[$property], $data[$property]);
                continue;
            }
            $this->$property = $data[$property];
        }
    }

    protected function assertConstructDataIsValid(array $data): void
    {
    }

    public static function getProperties(): array
    {
        $className = static::class;
        $cachedPropertyNames = BaseObjectMetadataCache::findPropertyNames($className);
        if ($cachedPropertyNames !== null) {
            return $cachedPropertyNames;
        }

        $propertyNames = array_keys(
            static::getPropertiesValues(),
        );
        BaseObjectMetadataCache::putPropertyNames($className, $propertyNames);

        return $propertyNames;
    }

    public static function getPropertiesValues(): array
    {
        $className = static::class;
        $cachedPropertiesValues = BaseObjectMetadataCache::findPropertiesValues($className);
        if ($cachedPropertiesValues !== null) {
            return $cachedPropertiesValues;
        }

        // get_class_vars() returns the declared default values (also for static
        // properties), so the result depends only on the class and is safe to keep.
        $propertiesValues = get_class_vars($className);
        BaseObjectMetadataCache::putPropertiesValues($className, $propertiesValues);

        return $propertiesValues;
    }

    public static function empty(): static
    {
        return static::fromArray([]);
    }

    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    public function toArray(): array
    {
        $data = [];
        $properties = static::getProperties();

        foreach ($properties as $property) {
            // Read the property once: every dynamic property read has a cost.
            $value = $this->$property;
            if ($value instanceof Arrayable) {
                $data[$property] = $value->toArray();
                continue;
            }
            if ($value instanceof BackedEnum) {
                $data[$property] = $value->value;
                continue;
            }
            $data[$property] = $value;
        }

        return $data;
    }

    public function isEquals(?Arrayable $obj): bool
    {
        if (!$obj) {
            return false;
        }

        return empty($this->diffAttributes($obj));
    }

    public function diffAttributes(?Arrayable $obj): array
    {
        if (!$obj) {
            return $this->toArray();
        }

        return $this->arrayDiffRecursive(
            $this->toArray(),
            $obj->toArray(),
        );
    }

    public function copy(array $data = []): static
    {
        return static::fromArray(array_merge($this->toArray(), $data));
    }

    private function arrayDiffRecursive(array $array1, array $array2): array
    {
        $difference = [];

        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key]) || !is_array($array2[$key])) {
                    // If the second array doesn't have the key or it's not an array
                    $difference[$key] = $value;
                } elseif (count($value) !== count($array2[$key])) {
                    // If arrays have different lengths
                    $difference[$key] = $value;
                } else {
                    // Recursively compare arrays
                    $new_diff = $this->arrayDiffRecursive($value, $array2[$key]);
                    if (!empty($new_diff)) {
                        $difference[$key] = $new_diff;
                    }
                }
            } else {
                // Check if the value is different or if it doesn't exist in the second array
                if (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                    if (is_float($value) && array_key_exists($key, $array2) && is_float($array2[$key])) {
                        // Handle float comparison
                        if ($this->areFloatNumbersEqual($value, $array2[$key])) {
                            continue;
                        }
                    }
                    $difference[$key] = $value;
                }
            }
        }

        // Now also check if there are keys in array2 that are not in array1 (e.g., extra data)
        foreach ($array2 as $key => $value) {
            if (!array_key_exists($key, $array1)) {
                $difference[$key] = null; // or $value, depending on how you want to handle missing keys
            }
        }

        return $difference;
    }

    private function areFloatNumbersEqual(float $value1, float $value2): bool
    {
        return (string)$value1 === (string)$value2;
    }

    /**
     * @param class-string<static> $className
     * @return array{fromArray: array<string, string>, enums: array<string, string>}
     */
    private static function getClassMetadata(string $className): array
    {
        $cachedClassMetadata = BaseObjectMetadataCache::findClassMetadata($className);
        if ($cachedClassMetadata !== null) {
            return $cachedClassMetadata;
        }

        $classMetadata = self::buildClassMetadata($className);
        BaseObjectMetadataCache::putClassMetadata($className, $classMetadata);

        return $classMetadata;
    }

    /**
     * @param class-string<static> $className
     * @return array{fromArray: array<string, string>, enums: array<string, string>}
     */
    private static function buildClassMetadata(string $className): array
    {
        $result = [
            'fromArray' => [],
            'enums' => [],
        ];
        $reflection = new ReflectionClass($className);
        foreach ($reflection->getProperties() as $property) {
            $propertyType = $property->getType();
            if (!$propertyType instanceof ReflectionNamedType) {
                continue;
            }
            if ($propertyType->isBuiltin()) {
                continue;
            }
            if (self::isFromArrayable($propertyType->getName())) {
                $result['fromArray'][$property->getName()] = $propertyType->getName();
                continue;
            }
            if (enum_exists($propertyType->getName())) {
                $result['enums'][$property->getName()] = $propertyType->getName();
            }
        }

        return $result;
    }

    private static function isFromArrayable(string $name): bool
    {
        $implementations = class_implements($name);
        return (bool)($implementations[FromArrayable::class] ?? null);
    }

    /**
     * An already built object (for example a DTO passed by a translator) is kept as it is.
     */
    private static function buildFromArrayableValue(string $className, mixed $value): mixed
    {
        if (is_object($value)) {
            return $value;
        }

        /** @var array<mixed> $value */
        return $className::fromArray($value);
    }

    /**
     * An enum case that is already built is kept as it is.
     */
    private static function buildEnumValue(string $className, mixed $value): mixed
    {
        if (is_object($value)) {
            return $value;
        }

        /** @var int|string $value */
        return $className::from($value);
    }
}
