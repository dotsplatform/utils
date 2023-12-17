<?php
/**
 * Description of TestBaseObjectWithUnitEnum.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject\Objects;

use Dots\Data\BaseObject;

class TestBaseObjectWithStringEnum extends BaseObject
{
    protected ?TestStringEnum $enum;

    public function getEnum(): ?TestStringEnum
    {
        return $this->enum;
    }

}
