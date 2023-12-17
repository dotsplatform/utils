<?php
/**
 * Description of TestBaseObjectWithUnitEnum.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject\Objects;

use Dots\Data\BaseObject;

class TestBaseObjectWithUnitEnum extends BaseObject
{
    protected ?TestUnitEnum $enum;

    public function getEnum(): ?TestUnitEnum
    {
        return $this->enum;
    }

}
