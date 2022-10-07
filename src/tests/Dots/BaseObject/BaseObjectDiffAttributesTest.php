<?php
/**
 * Description of BaseObjectDiffAttributesTest.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Dots\Tests\Dots\BaseObject;

use Dots\Tests\Dots\BaseObject\Objects\TestBaseObject;
use Dots\Tests\Dots\BaseObject\Objects\TestBaseObjectWithArray;
use Tests\TestCase;

class BaseObjectDiffAttributesTest extends TestCase
{
    /**
     * @group unit
     * @group object
     */
    public function testDiffAttributesFullyDifferent(): void
    {
        $old = TestBaseObject::fromArray([
            'id' => $this->generateUuid(),
            'name' => $this->generateUuid(),
        ]);
        $data = [
            'id' => $this->generateUuid(),
            'name' => $this->generateUuid(),
        ];
        $new = TestBaseObject::fromArray($data);

        $diff = $new->diffAttributes($old);

        $this->assertEquals($data, $diff);
    }

    /**
     * @group unit
     * @group object
     */
    public function testDiffAttributesOneAttrDiffer(): void
    {
        $old = TestBaseObject::fromArray([
            'id' => $this->generateUuid(),
            'name' => $this->generateUuid(),
        ]);
        $data = [
            'id' => $old->getId(),
            'name' => $this->generateUuid(),
        ];
        $new = TestBaseObject::fromArray($data);
        $diff = $new->diffAttributes($old);

        $this->assertEquals([
            'name' => $new->getName(),
        ], $diff);
    }

    /**
     * @group unit
     * @group object
     */
    public function testDiffAttributesOldWasNull(): void
    {
        $data = [
            'id' => $this->generateUuid(),
            'name' => $this->generateUuid(),
        ];
        $new = TestBaseObject::fromArray($data);
        $diff = $new->diffAttributes(null);

        $this->assertEquals($new->toArray(), $diff);
    }

    /**
     * @group unit
     * @group object
     */
    public function testDiffAttributesReturnsEmptyArrayIfEquals(): void
    {
        $data = [
            'id' => $this->generateUuid(),
            'name' => $this->generateUuid(),
        ];
        $old = TestBaseObject::fromArray($data);
        $new = TestBaseObject::fromArray($data);
        $diff = $new->diffAttributes($old);

        $this->assertEmpty($diff);
    }

    /**
     * @group unit
     * @group object
     */
    public function testDiffAttributesReturnsEmptyArrayIfEqualsWithArray(): void
    {
        $data = [
            'data' => [
                [
                    'longitude' => 31.252028867728,
                    'latitude' => 51.543554529719,
                ],
                [
                    'longitude' => 51.492096468216,
                    'latitude' => 31.240528755553,
                ],
                [
                    'latitude' => 51.543554529719,
                    'longitude' => 31.370674876778,
                ],
            ],
        ];
        $old = TestBaseObjectWithArray::fromArray($data);
        $new = TestBaseObjectWithArray::fromArray($data);
        $diff = $new->diffAttributes($old);

        $this->assertEmpty($diff);
    }

    /**
     * @group unit
     * @group object
     */
    public function testDiffAttributesReturnsNotEmptyArrayIfNotEqualsWithArray(): void
    {
        $dataNew = [
            'data' => [
                'price' => 12.5,
            ],
        ];
        $dataOld = [
            'data' => [

            ],
        ];
        $old = TestBaseObjectWithArray::fromArray($dataOld);
        $new = TestBaseObjectWithArray::fromArray($dataNew);
        $diff = $new->diffAttributes($old);
        $this->assertNotEmpty($diff['data']['price']);
    }
}
