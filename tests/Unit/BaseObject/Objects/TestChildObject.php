<?php
/**
 * Description of TestChildObject.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject\Objects;

class TestChildObject extends TestParentObject
{
    protected TestIntEnum $status = TestIntEnum::ONE;

    protected ?TestCollection $items = null;

    private string $childPrivate = 'private';

    public function getStatus(): TestIntEnum
    {
        return $this->status;
    }

    public function getItems(): ?TestCollection
    {
        return $this->items;
    }
}
