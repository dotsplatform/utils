<?php
/**
 * Description of BaseObjectHydrationTest.php.
 *
 * Pins the fromArray()/toArray() rules the constructor fast path must keep:
 * missing and null values take the declared default, built objects and enum
 * cases are kept as they are, arrays and scalars are converted.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject;

use Dots\Data\BaseObjectMetadataCache;
use Tests\TestCase;
use Tests\Unit\BaseObject\Objects\TestBaseObject;
use Tests\Unit\BaseObject\Objects\TestChildObject;
use Tests\Unit\BaseObject\Objects\TestCollection;
use Tests\Unit\BaseObject\Objects\TestIntEnum;

class BaseObjectHydrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        BaseObjectMetadataCache::flush();
    }

    /**
     * @group unit
     * @group object
     */
    public function testNullValuesTakeDeclaredDefaults(): void
    {
        $child = TestChildObject::fromArray([
            'id' => 'child',
            'owner' => null,
            'status' => null,
            'items' => null,
        ]);

        $this->assertNull($child->getOwner());
        $this->assertSame(TestIntEnum::ONE, $child->getStatus());
        $this->assertNull($child->getItems());
    }

    /**
     * @group unit
     * @group object
     */
    public function testBuiltObjectsAndEnumCasesAreKeptAsTheyAre(): void
    {
        $owner = TestBaseObject::fromArray(['id' => 'owner']);
        $items = new TestCollection([1, 2]);

        $child = TestChildObject::fromArray([
            'id' => 'child',
            'owner' => $owner,
            'status' => TestIntEnum::TWO,
            'items' => $items,
        ]);

        $this->assertSame($owner, $child->getOwner());
        $this->assertSame(TestIntEnum::TWO, $child->getStatus());
        $this->assertSame($items, $child->getItems());
    }

    /**
     * @group unit
     * @group object
     */
    public function testArraysAndScalarsAreConvertedToObjectsAndEnums(): void
    {
        $child = TestChildObject::fromArray([
            'id' => 'child',
            'owner' => ['id' => 'owner'],
            'status' => 2,
            'items' => [1, 2],
        ]);

        $this->assertSame('owner', $child->getOwner()?->getId());
        $this->assertSame(TestIntEnum::TWO, $child->getStatus());
        $this->assertSame([1, 2], $child->getItems()?->all());
    }

    /**
     * @group unit
     * @group object
     */
    public function testToArrayConvertsNestedObjectsAndEnums(): void
    {
        $child = TestChildObject::fromArray([
            'id' => 'child',
            'owner' => ['id' => 'owner'],
            'status' => 2,
            'items' => [1, 2],
        ]);

        $this->assertSame(
            [
                'status' => 2,
                'items' => [1, 2],
                'id' => 'child',
                'owner' => ['id' => 'owner', 'name' => 'Test'],
            ],
            $child->toArray(),
        );
    }

    /**
     * @group unit
     * @group object
     */
    public function testPropertyNamesAreTheSameAfterCacheFlush(): void
    {
        $propertyNamesBeforeFlush = TestChildObject::getProperties();
        BaseObjectMetadataCache::flush();

        $this->assertSame(['status', 'items', 'id', 'owner'], $propertyNamesBeforeFlush);
        $this->assertSame($propertyNamesBeforeFlush, TestChildObject::getProperties());
        $this->assertSame(
            $propertyNamesBeforeFlush,
            array_keys(TestChildObject::getPropertiesValues()),
        );
    }
}
