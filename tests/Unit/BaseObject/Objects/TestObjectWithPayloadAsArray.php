<?php
/**
 * Description of TestObjectWithPayloadAsArray.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject\Objects;

use Dots\Data\DTO;

class TestObjectWithPayloadAsArray extends DTO
{
    protected ?array $payload = null;

    public function getPayload(): mixed
    {
        return $this->payload;
    }
}
