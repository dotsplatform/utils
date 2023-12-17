<?php
/**
 * Description of TestBaseObjectWithUnitEnum.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject\Objects;

use Dots\Data\BaseObject;

class TestBaseObjectEnumWithOverwritingFromArray extends BaseObject
{
    protected ?TestIntEnum $enum;

    public static function fromArray(array $data): static
    {
        $data['enum'] = $data['enum'] ?? TestIntEnum::ONE;
        return parent::fromArray($data);
    }

    public function getEnum(): ?TestIntEnum
    {
        return $this->enum;
    }
}
