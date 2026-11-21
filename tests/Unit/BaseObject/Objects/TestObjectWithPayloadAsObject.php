<?php
/**
 * Description of TestObjectWithPayloadAsObject.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject\Objects;

use Dots\Data\DTO;

class TestObjectWithPayloadAsObject extends DTO
{
    protected ?TestBaseObject $payload = null;

    public function getPayload(): mixed
    {
        return $this->payload;
    }
}
