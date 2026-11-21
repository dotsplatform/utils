<?php
/**
 * Description of TestObjectWithPayloadAsEnum.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject\Objects;

use Dots\Data\DTO;

class TestObjectWithPayloadAsEnum extends DTO
{
    protected ?TestStringEnum $payload = null;

    public function getPayload(): mixed
    {
        return $this->payload;
    }
}
