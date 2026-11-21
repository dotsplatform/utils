<?php
/**
 * Description of TestParentObject.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject\Objects;

use Dots\Data\DTO;

class TestParentObject extends DTO
{
    protected string $id;

    protected ?TestBaseObject $owner = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function getOwner(): ?TestBaseObject
    {
        return $this->owner;
    }
}
