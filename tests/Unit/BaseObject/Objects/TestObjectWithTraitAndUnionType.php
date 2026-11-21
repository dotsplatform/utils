<?php
/**
 * Description of TestObjectWithTraitAndUnionType.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject\Objects;

use Dots\Data\DTO;

class TestObjectWithTraitAndUnionType extends DTO
{
    use TestPropertiesTrait;

    protected int|string|null $unionValue = null;

    protected TestBaseObject|array|null $unionObject = null;

    public function getTraitObject(): ?TestBaseObject
    {
        return $this->traitObject;
    }

    public function getTraitName(): string
    {
        return $this->traitName;
    }

    public function getUnionValue(): int|string|null
    {
        return $this->unionValue;
    }

    public function getUnionObject(): TestBaseObject|array|null
    {
        return $this->unionObject;
    }
}
