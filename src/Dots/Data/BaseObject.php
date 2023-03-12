<?php
/**
 * Description of BaseObject.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Dots\Data;

use Illuminate\Contracts\Support\Arrayable;
use ReflectionClass;

abstract class BaseObject implements Arrayable, FromArrayable
{
    private function __construct(
        array $data,
    ) {
        $this->assertConstructDataIsValid($data);
        $properties = $this->getPropertiesValues();
        $objProperties = $this->getObjectionableProperties();

        foreach ($properties as $property => $defaultValue) {
            if ($this->isNeedToCreateFromArrayableObject($objProperties, $data, $property)) {
                $this->$property = $objProperties[$property]::fromArray($data[$property]);
                continue;
            }
            $this->$property = $data[$property] ?? $defaultValue;
        }
    }

    protected function assertConstructDataIsValid(array $data): void
    {
    }

    public static function getProperties(): array
    {
        return array_keys(
            static::getPropertiesValues(),
        );
    }

    public static function getPropertiesValues(): array
    {
        return get_class_vars(static::class);
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
        $properties = $this->getProperties();

        foreach ($properties as $property) {
            if ($this->$property instanceof Arrayable) {
                $data[$property] = $this->$property->toArray();
            } else {
                $data[$property] = $this->$property;
            }
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

    private function arrayDiffRecursive(array $array1, array $array2): array
    {
        $difference = [];
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key]) || !is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = $this->arrayDiffRecursive($value, $array2[$key]);
                    if (!empty($new_diff)) {
                        $difference[$key] = $new_diff;
                    }
                }
            } else {
                if (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                    if (is_float($value) && array_key_exists($key, $array2) && is_float($array2[$key])) {
                        if ($this->areFloatNumbersEqual($value, $array2[$key])) {
                            continue;
                        }
                    }
                    $difference[$key] = $value;
                }
            }
        }

        return $difference;
    }

    private function areFloatNumbersEqual(float $value1, float $value2): bool
    {
        return (string)$value1 === (string)$value2;
    }

    private function getObjectionableProperties(): array
    {
        $result = [];
        $reflection = new ReflectionClass($this);
        foreach ($reflection->getProperties() as $property) {
            if ($property->getType()->isBuiltin()) {
                continue;
            }

            if (!$this->isFromArrayable($property->getType()->getName())) {
                continue;
            }

            $result[$property->getName()] = $property->getType()->getName();
        }

        return $result;
    }

    private function isFromArrayable(string $name): bool
    {
        $implementations = class_implements($name);
        return (bool)($implementations[FromArrayable::class] ?? null);
    }

    private function isNeedToCreateFromArrayableObject(
        array $objProperties,
        array $data,
        string $property,
    ): bool {
        if (!isset($data[$property])) {
            return false;
        }

        if (is_object($data[$property])) {
            return false;
        }

        return !empty($objProperties[$property]);
    }
}
