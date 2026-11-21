<?php
/**
 * Description of BaseObjectMetadataCacheTest.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject;

use Dots\Data\BaseObjectMetadataCache;
use InvalidArgumentException;
use Tests\TestCase;
use Tests\Unit\BaseObject\Objects\TestBaseObject;
use Tests\Unit\BaseObject\Objects\TestChildObject;
use Tests\Unit\BaseObject\Objects\TestCollection;
use Tests\Unit\BaseObject\Objects\TestIntEnum;
use Tests\Unit\BaseObject\Objects\TestObjectWithPayloadAsArray;
use Tests\Unit\BaseObject\Objects\TestObjectWithPayloadAsEnum;
use Tests\Unit\BaseObject\Objects\TestObjectWithPayloadAsObject;
use Tests\Unit\BaseObject\Objects\TestObjectWithStaticProperty;
use Tests\Unit\BaseObject\Objects\TestObjectWithTraitAndUnionType;
use Tests\Unit\BaseObject\Objects\TestObjectWithValidation;
use Tests\Unit\BaseObject\Objects\TestParentObject;
use Tests\Unit\BaseObject\Objects\TestStringEnum;

class BaseObjectMetadataCacheTest extends TestCase
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
    public function testCacheDoesNotAddPropertiesToObjects(): void
    {
        TestBaseObject::fromArray(['id' => 'first']);

        $this->assertSame(['id', 'name'], TestBaseObject::getProperties());
        $this->assertSame(['id' => 'second', 'name' => 'Test'], TestBaseObject::fromArray(['id' => 'second'])->toArray());
    }

    /**
     * @group unit
     * @group object
     */
    public function testParentCreatedFirstDoesNotShareMetadataWithChild(): void
    {
        $parent = TestParentObject::fromArray([
            'id' => 'parent',
            'owner' => ['id' => 'owner'],
        ]);
        $child = TestChildObject::fromArray([
            'id' => 'child',
            'owner' => ['id' => 'owner'],
            'status' => 2,
            'items' => [1, 2],
        ]);

        $this->assertParentAndChildAreBuiltCorrectly($parent, $child);
    }

    /**
     * @group unit
     * @group object
     */
    public function testChildCreatedFirstDoesNotShareMetadataWithParent(): void
    {
        $child = TestChildObject::fromArray([
            'id' => 'child',
            'owner' => ['id' => 'owner'],
            'status' => 2,
            'items' => [1, 2],
        ]);
        $parent = TestParentObject::fromArray([
            'id' => 'parent',
            'owner' => ['id' => 'owner'],
        ]);

        $this->assertParentAndChildAreBuiltCorrectly($parent, $child);
    }

    /**
     * @group unit
     * @group object
     */
    public function testChildPrivatePropertyIsNotFilled(): void
    {
        $this->assertSame(['status', 'items', 'id', 'owner'], TestChildObject::getProperties());
        $this->assertSame(['id', 'owner'], TestParentObject::getProperties());
    }

    /**
     * @group unit
     * @group object
     */
    public function testClassesWithSamePropertyNameKeepOwnTypes(): void
    {
        $payload = ['id' => 'payload-id', 'name' => 'payload-name'];

        $asObject = TestObjectWithPayloadAsObject::fromArray(['payload' => $payload]);
        $asArray = TestObjectWithPayloadAsArray::fromArray(['payload' => $payload]);
        $asEnum = TestObjectWithPayloadAsEnum::fromArray(['payload' => 'two']);
        $asObjectAgain = TestObjectWithPayloadAsObject::fromArray(['payload' => $payload]);

        $this->assertInstanceOf(TestBaseObject::class, $asObject->getPayload());
        $this->assertSame($payload, $asArray->getPayload());
        $this->assertSame(TestStringEnum::TWO, $asEnum->getPayload());
        $this->assertInstanceOf(TestBaseObject::class, $asObjectAgain->getPayload());
    }

    /**
     * @group unit
     * @group object
     */
    public function testNestedObjectsAreBuiltFromArraysAndKeptAsObjects(): void
    {
        $owner = TestBaseObject::fromArray(['id' => 'owner']);

        $fromArray = TestParentObject::fromArray(['id' => 'a', 'owner' => ['id' => 'owner']]);
        $fromObject = TestParentObject::fromArray(['id' => 'b', 'owner' => $owner]);
        $withoutOwner = TestParentObject::fromArray(['id' => 'c']);

        $this->assertEquals($owner, $fromArray->getOwner());
        $this->assertSame($owner, $fromObject->getOwner());
        $this->assertNull($withoutOwner->getOwner());
        $this->assertSame(
            ['id' => 'a', 'owner' => ['id' => 'owner', 'name' => 'Test']],
            $fromArray->toArray(),
        );
    }

    /**
     * @group unit
     * @group object
     */
    public function testTraitPropertiesAndUnionTypes(): void
    {
        $object = TestObjectWithTraitAndUnionType::fromArray([
            'traitObject' => ['id' => 'trait-object'],
            'unionValue' => 5,
            'unionObject' => ['id' => 'stays-array'],
        ]);

        $this->assertInstanceOf(TestBaseObject::class, $object->getTraitObject());
        $this->assertSame('trait', $object->getTraitName());
        $this->assertSame(5, $object->getUnionValue());
        // Union types are not resolved to objects (only named types are), same as before the cache.
        $this->assertSame(['id' => 'stays-array'], $object->getUnionObject());
        $this->assertSame(
            ['unionValue', 'unionObject', 'traitObject', 'traitName'],
            TestObjectWithTraitAndUnionType::getProperties(),
        );
    }

    /**
     * @group unit
     * @group object
     */
    public function testDefaultsAreNotSharedBetweenInstances(): void
    {
        $first = TestChildObject::fromArray(['id' => 'first']);
        $second = TestChildObject::fromArray(['id' => 'second', 'status' => 2]);
        $third = TestChildObject::fromArray(['id' => 'third']);

        $this->assertSame(TestIntEnum::ONE, $first->getStatus());
        $this->assertSame(TestIntEnum::TWO, $second->getStatus());
        $this->assertSame(TestIntEnum::ONE, $third->getStatus());
        $this->assertNull($third->getItems());
    }

    /**
     * @group unit
     * @group object
     */
    public function testStaticPropertyKeepsDeclaredDefaultLikeGetClassVars(): void
    {
        $this->assertSame('initial', TestObjectWithStaticProperty::getPropertiesValues()['staticValue']);

        TestObjectWithStaticProperty::$staticValue = 'changed';

        // get_class_vars() returns declared defaults, not the current static value.
        $this->assertSame('initial', TestObjectWithStaticProperty::getPropertiesValues()['staticValue']);
        TestObjectWithStaticProperty::$staticValue = 'initial';
    }

    /**
     * @group unit
     * @group object
     */
    public function testValidationRunsForEveryInstance(): void
    {
        $this->assertSame('valid', TestObjectWithValidation::fromArray(['id' => 'valid'])->getId());

        $this->expectException(InvalidArgumentException::class);
        TestObjectWithValidation::fromArray([]);
    }

    private function assertParentAndChildAreBuiltCorrectly(TestParentObject $parent, TestChildObject $child): void
    {
        $this->assertSame(['id', 'owner'], TestParentObject::getProperties());
        $this->assertSame(['status', 'items', 'id', 'owner'], TestChildObject::getProperties());

        $this->assertInstanceOf(TestBaseObject::class, $parent->getOwner());
        $this->assertInstanceOf(TestBaseObject::class, $child->getOwner());
        $this->assertSame(TestIntEnum::TWO, $child->getStatus());
        $this->assertInstanceOf(TestCollection::class, $child->getItems());

        $this->assertSame(
            ['id' => 'parent', 'owner' => ['id' => 'owner', 'name' => 'Test']],
            $parent->toArray(),
        );
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
}
