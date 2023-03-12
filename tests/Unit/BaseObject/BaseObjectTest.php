<?php
/**
 * Description of BaseObjectTest.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject;

use Dots\Data\FromArrayable;
use Tests\TestCase;
use Tests\Unit\BaseObject\Objects\TestBaseObject;
use Tests\Unit\BaseObject\Objects\TestBaseObjectWithCollectionAndObjectAndArray;
use Tests\Unit\BaseObject\Objects\TestBaseObjectWithIncludedObject;
use Tests\Unit\BaseObject\Objects\TestCollection;
use Tests\Unit\BaseObject\Objects\TestObjectWithIncludedObjAndCollection;

class BaseObjectTest extends TestCase
{
    /**
     * @group unit
     * @group object
     */
    public function testCreatesObject(): void
    {
        $data = [
            'id' => $this->generateUuid(),
            'name' => $this->generateUuid(),
        ];
        $object = TestBaseObject::fromArray($data);
        $this->assertEquals($data['id'], $object->getId());
        $this->assertEquals($data['name'], $object->getName());
    }

    /**
     * @group unit
     * @group object
     */
    public function testSetDefaultPropertyValue(): void
    {
        $object = TestBaseObject::fromArray([
            'id' => $this->generateUuid(),
        ]);
        $this->assertEquals('Test', $object->getName());
    }

    /**
     * @group unit
     * @group object123
     */
    public function testExpectsIncludedObjectFilledValue(): void
    {
        $object = TestBaseObjectWithIncludedObject::fromArray([
            'id' => $this->generateUuid(),
            'object' => [
                'name' => 'name',
                'id' => $this->generateUuid(),
            ],
        ]);

        $this->assertInstanceOf(FromArrayable::class, $object->getObject());
    }

    /**
     * @group unit
     * @group object123
     */
    public function testExpectsIncludedObjectIsNullByDefault(): void
    {
        $object = TestBaseObjectWithIncludedObject::fromArray([
            'id' => $this->generateUuid(),
        ]);

        $this->assertNull($object->getObject());
    }

    /**
     * @group unit
     * @group object123
     */
    public function testExpectsIncludedObjectIsNullIfPassed(): void
    {
        $object = TestBaseObjectWithIncludedObject::fromArray([
            'id' => $this->generateUuid(),
            'object' => null
        ]);

        $this->assertNull($object->getObject());
    }

    /**
     * @group unit
     * @group object1234
     */
    public function testExpectsIncludedObjectIsPassedToDTOAsObject(): void
    {
        $object = TestBaseObjectWithIncludedObject::fromArray([
            'id' => $this->generateUuid(),
            'object' => TestBaseObject::fromArray([
                'name' => 'name',
                'id' => $this->generateUuid(),
            ])
        ]);

        $this->assertInstanceOf(FromArrayable::class, $object->getObject());
    }

    /**
     * @group unit
     * @group object123
     */
    public function testExpectsIncludedObjectAndCollectionFilled(): void
    {
        $collectionData = [
            'data' => [],
        ];
        $objData = [
            'name' => 'name',
            'id' => $this->generateUuid(),
        ];
        $object = TestObjectWithIncludedObjAndCollection::fromArray([
            'id' => $this->generateUuid(),
            'collection' => $collectionData,
            'object' => $objData,
        ]);

        $this->assertInstanceOf(FromArrayable::class, $object->getObject());
        $this->assertInstanceOf(TestCollection::class, $object->getCollection());
        $this->assertEquals($objData, $object->getObject()->toArray());
        $this->assertEquals($collectionData, $object->getCollection()->toArray());
    }

    /**
     * @group unit
     * @group object123
     */
    public function testExpectsIncludedCollectionFilledPassedAsObject(): void
    {
        $collectionData = [
            'data' => [],
        ];
        $objData = [
            'name' => 'name',
            'id' => $this->generateUuid(),
        ];
        $object = TestObjectWithIncludedObjAndCollection::fromArray([
            'id' => $this->generateUuid(),
            'collection' => TestCollection::fromArray($collectionData),
            'object' => $objData,
        ]);

        $this->assertInstanceOf(TestCollection::class, $object->getCollection());
    }

    /**
     * @group unit
     * @group object123
     */
    public function testExpectsIncludedObjectFilledAndCollectionIsNullByDefault(): void
    {
        $objData = [
            'name' => 'name',
            'id' => $this->generateUuid(),
        ];
        $object = TestObjectWithIncludedObjAndCollection::fromArray([
            'id' => $this->generateUuid(),
            'object' => $objData,
        ]);
        $this->assertNull($object->getCollection());
    }

    /**
     * @group unit
     * @group object123
     */
    public function testExpectsIncludedObjectFilledAndCollectionIsNullIfPassed(): void
    {
        $objData = [
            'name' => 'name',
            'id' => $this->generateUuid(),
        ];
        $object = TestObjectWithIncludedObjAndCollection::fromArray([
            'id' => $this->generateUuid(),
            'collection' => null,
            'object' => $objData,
        ]);
        $this->assertNull($object->getCollection());
    }

    /**
     * @group unit
     * @group object123
     */
    public function testExpectsIncludedObjectAndCollectionAndArrayFilled(): void
    {
        $arrayData = [
            'key' => 'Hello I am array',
        ];
        $object = TestBaseObjectWithCollectionAndObjectAndArray::fromArray([
            'id' => $this->generateUuid(),
            'collection' => [],
            'object' => [
                'name' => 'name',
                'id' => $this->generateUuid(),
            ],
            'data' => $arrayData,
        ]);
        $this->assertEquals($arrayData, $object->getData());
    }
}
