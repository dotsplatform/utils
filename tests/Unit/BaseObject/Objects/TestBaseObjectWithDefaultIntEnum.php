<?php
/**
 * Description of TestBaseObjectWithUnitEnum.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject\Objects;

use Dots\Data\BaseObject;

class TestBaseObjectWithDefaultIntEnum extends BaseObject
{
    protected TestIntEnum $enum = TestIntEnum::ONE;

    public function getEnum(): TestIntEnum
    {
        return $this->enum;
    }
}
