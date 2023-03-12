<?php
/**
 * Description of TestBaseObjectWithCollectionAndObjectAndArray.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject\Objects;


use Dots\Data\DTO;

class TestBaseObjectWithCollectionAndObjectAndArray extends DTO
{
    protected string $id;
    protected TestBaseObject $object;
    protected TestCollection $collection;
    protected array $data;

    public function getId(): string
    {
        return $this->id;
    }

    public function getObject(): TestBaseObject
    {
        return $this->object;
    }

    public function getCollection(): TestCollection
    {
        return $this->collection;
    }

    public function getData(): array
    {
        return $this->data;
    }
}