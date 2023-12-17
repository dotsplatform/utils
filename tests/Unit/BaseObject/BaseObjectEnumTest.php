<?php
/**
 * Description of BaseObjectTest.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject;

use Tests\TestCase;
use Tests\Unit\BaseObject\Objects\TestBaseObjectEnumWithOverwritingFromArray;
use Tests\Unit\BaseObject\Objects\TestBaseObjectWithDefaultIntEnum;
use Tests\Unit\BaseObject\Objects\TestBaseObjectWithIntEnum;
use Tests\Unit\BaseObject\Objects\TestBaseObjectWithStringEnum;
use Tests\Unit\BaseObject\Objects\TestBaseObjectWithUnitEnum;
use Tests\Unit\BaseObject\Objects\TestIntEnum;
use Tests\Unit\BaseObject\Objects\TestStringEnum;
use Tests\Unit\BaseObject\Objects\TestUnitEnum;

class BaseObjectEnumTest extends TestCase
{

    /**
     * @dataProvider provideTestWithIntEnumProperty
     */
    public function testWithIntEnum(array $data, ?TestIntEnum $expected, array $expectedData): void
    {
        $obj = TestBaseObjectWithIntEnum::fromArray($data);
        $this->assertSame($expected, $obj->getEnum());
        $this->assertSame($expectedData, $obj->toArray());
    }

    public function testTestBaseObjectWithDefaultIntEnum(): void
    {
        $obj = TestBaseObjectWithDefaultIntEnum::empty();
        $this->assertSame(TestIntEnum::ONE, $obj->getEnum());

        $obj = TestBaseObjectWithDefaultIntEnum::fromArray([
            'enum' => TestIntEnum::TWO,
        ]);
        $this->assertSame(TestIntEnum::TWO, $obj->getEnum());
    }

    public function testBaseObjectEnumWithOverwritingFromArray(): void
    {
        $obj = TestBaseObjectEnumWithOverwritingFromArray::fromArray([
            'enum' => TestIntEnum::TWO,
        ]);

        $this->assertSame(TestIntEnum::TWO, $obj->getEnum());
    }

    /**
     * @dataProvider provideTestWithStringEnumProperty
     */
    public function testWithStringEnum(array $data, ?TestStringEnum $expected, array $expectedData): void
    {
        $obj = TestBaseObjectWithStringEnum::fromArray($data);
        $this->assertSame($expected, $obj->getEnum());
        $this->assertSame($expectedData, $obj->toArray());
    }

    /**
     * @dataProvider provideTestWithUnitEnumProperty
     */
    public function testWithUnitEnum(array $data, ?TestUnitEnum $expected, array $expectedData): void
    {
        $obj = TestBaseObjectWithUnitEnum::fromArray($data);
        $this->assertSame($expected, $obj->getEnum());
        $this->assertSame($expectedData, $obj->toArray());
    }

    public static function provideTestWithIntEnumProperty(): array
    {
        return [
            'from int' => [
                'data' => [
                    'enum' => 1,
                ],
                'expected' => TestIntEnum::ONE,
                'expectedToArray' => [
                    'enum' => 1,
                ],
            ],
            'from int 2' => [
                'data' => [
                    'enum' => 2,
                ],
                'expected' => TestIntEnum::TWO,
                'expectedToArray' => [
                    'enum' => 2,
                ],
            ],
            'from enum' => [
                'data' => [
                    'enum' => TestIntEnum::ONE,
                ],
                'expected' => TestIntEnum::ONE,
                'expectedToArray' => [
                    'enum' => 1,
                ],
            ],
            'from enum 2' => [
                'data' => [
                    'enum' => TestIntEnum::TWO,
                ],
                'expected' => TestIntEnum::TWO,
                'expectedToArray' => [
                    'enum' => 2,
                ],
            ],
            'nullable' => [
                'data' => [
                    'enum' => null,
                ],
                'expected' => null,
                'expectedToArray' => [
                    'enum' => null,
                ],
            ],
            'test empty data uses default' => [
                'data' => [],
                'expected' => null,
                'expectedToArray' => [
                    'enum' => null,
                ],
            ],
        ];
    }

    public static function provideTestWithStringEnumProperty(): array
    {
        return [
            'from int' => [
                'data' => [
                    'enum' => 'one',
                ],
                'expected' => TestStringEnum::ONE,
                'expectedToArray' => [
                    'enum' => 'one',
                ],
            ],
            'from int 2' => [
                'data' => [
                    'enum' => 'two',
                ],
                'expected' => TestStringEnum::TWO,
                'expectedToArray' => [
                    'enum' => 'two',
                ],
            ],
            'from enum' => [
                'data' => [
                    'enum' => TestStringEnum::ONE,
                ],
                'expected' => TestStringEnum::ONE,
                'expectedToArray' => [
                    'enum' => 'one',
                ],
            ],
            'from enum 2' => [
                'data' => [
                    'enum' => TestStringEnum::TWO,
                ],
                'expected' => TestStringEnum::TWO,
                'expectedToArray' => [
                    'enum' => 'two',
                ],
            ],
            'nullable' => [
                'data' => [
                    'enum' => null,
                ],
                'expected' => null,
                'expectedToArray' => [
                    'enum' => null,
                ],
            ],
            'test empty data uses default' => [
                'data' => [],
                'expected' => null,
                'expectedToArray' => [
                    'enum' => null,
                ],
            ],
        ];
    }

    public static function provideTestWithUnitEnumProperty(): array
    {
        return [
            'from int' => [
                'data' => [
                    'enum' => TestUnitEnum::ONE,
                ],
                'expected' => TestUnitEnum::ONE,
                'expectedToArray' => [
                    'enum' => TestUnitEnum::ONE,
                ],
            ],
            'from int 2' => [
                'data' => [
                    'enum' => TestUnitEnum::TWO,
                ],
                'expected' => TestUnitEnum::TWO,
                'expectedToArray' => [
                    'enum' => TestUnitEnum::TWO,
                ],
            ],
            'nullable' => [
                'data' => [
                    'enum' => null,
                ],
                'expected' => null,
                'expectedToArray' => [
                    'enum' => null,
                ],
            ],
            'test empty data uses default' => [
                'data' => [],
                'expected' => null,
                'expectedToArray' => [
                    'enum' => null,
                ],
            ],
        ];
    }

}
