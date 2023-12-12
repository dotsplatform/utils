<?php
/**
 * Description of ArrayFieldsToJsonCasterTest.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Unit\Utils\Casters;


use Dots\Utils\Casters\ArrayFieldsToJsonCaster;
use Illuminate\Support\Collection;
use RuntimeException;
use Tests\TestCase;

class ArrayFieldsToJsonCasterTest extends TestCase
{
    private function getArrayFieldsToJsonCaster(): ArrayFieldsToJsonCaster
    {
        return new ArrayFieldsToJsonCaster();
    }

    public function testCastArrayFieldsToJsonExpectsCastedWONestedArrayField(): void
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $result = $this->getArrayFieldsToJsonCaster()->castArrayFieldsToJson($data);
        $this->assertEquals($data, $result);
    }

    public function testCastArrayFieldsToJsonExpectsNestedArrayFieldCasted(): void
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
            'nestedKey' => [
                'nestedKey1' => 'nestedValue1',
                'nestedKey2' => 'nestedValue2',
            ],
        ];
        $expectedData = $data;
        $expectedData['nestedKey'] = json_encode($expectedData['nestedKey']);

        $result = $this->getArrayFieldsToJsonCaster()->castArrayFieldsToJson($data);
        $this->assertEquals($expectedData, $result);
    }

    public function testCastItemsArrayFieldsToJsonExpectsCastedWONestedArrayField(): void
    {
        $data = [
            [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
            [
                'key3' => 'value3',
                'key4' => 'value4',
            ],

        ];

        $result = $this->getArrayFieldsToJsonCaster()->castItemsArrayFieldsToJson($data);
        $this->assertEquals($data, $result);
    }

    public function testCastItemsArrayFieldsToJsonExpectsNestedArrayFieldCasted(): void
    {
        $data = [
            [
                'key1' => 'value1',
                'key2' => 'value2',
                'nestedKey' => [
                    'nestedKey1' => 'nestedValue1',
                    'nestedKey2' => 'nestedValue2',
                ],
            ],
            [
                'key3' => 'value3',
                'key4' => 'value4',
                'nestedKey' => [
                    'nestedKey3' => 'nestedValue3',
                    'nestedKey4' => 'nestedValue4',
                ],
            ],
        ];
        $expectedData = $data;
        foreach ($expectedData as $key => $value) {
            $expectedData[$key]['nestedKey'] = json_encode($expectedData[$key]['nestedKey']);
        }

        $result = $this->getArrayFieldsToJsonCaster()->castItemsArrayFieldsToJson($data);
        $this->assertEquals($expectedData, $result);
    }

    public function testCastItemsArrayFieldsToJsonExpectsOkIfArrayablePassed(): void
    {
        $data = [
            Collection::make([
                'key1' => 'value1',
                'key2' => 'value2',
                'nestedKey' => [
                    'nestedKey1' => 'nestedValue1',
                    'nestedKey2' => 'nestedValue2',
                ],
            ]),
        ];
        $expectedData = $data;
        $expectedData[0] = $expectedData[0]->toArray();
        $expectedData[0]['nestedKey'] = json_encode($expectedData[0]['nestedKey']);

        $result = $this->getArrayFieldsToJsonCaster()->castItemsArrayFieldsToJson($data);
        $this->assertEquals($expectedData, $result);
    }

    public function testCastItemsArrayFieldsToJsonExpectsFailIfArraySubItemIsNotArray(): void
    {
        $this->expectException(RuntimeException::class);
        $data = [
            'key1' => 'value1',
        ];

        $this->getArrayFieldsToJsonCaster()->castItemsArrayFieldsToJson($data);
    }
}