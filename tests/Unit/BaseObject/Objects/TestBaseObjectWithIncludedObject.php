<?php
/**
 * Description of TestBaseObjectWithIncludedObject.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject\Objects;


use Dots\Data\DTO;

class TestBaseObjectWithIncludedObject extends DTO
{
    protected string $id;
    protected ?TestBaseObject $object;

    public function getId(): string
    {
        return $this->id;
    }

    public function getObject(): ?TestBaseObject
    {
        return $this->object;
    }
}