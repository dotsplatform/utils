<?php
/**
 * Description of PositionTest.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Unit\Distance\DTO;

use Dots\Distance\Position;
use Tests\TestCase;

class PositionTest extends TestCase
{
    public function testPositionCreatesFromArray(): void
    {
        $longitude = 5;
        $latitude = 2;

        $position = Position::fromArray([
            'longitude' => $longitude,
            'latitude' => $latitude,
        ]);
        $this->assertEquals($latitude, $position->getLatitude());
        $this->assertEquals($longitude, $position->getLongitude());
    }

    public function testPositionCreatesFromLonLat(): void
    {
        $longitude = 5;
        $latitude = 2;

        $position = Position::fromLonLat($longitude, $latitude);
        $this->assertEquals($latitude, $position->getLatitude());
        $this->assertEquals($longitude, $position->getLongitude());
    }

    public function testPositionCreatesEmptyWithNull(): void
    {
        $position = Position::empty();
        $this->assertNull($position->getLatitude());
        $this->assertNull($position->getLongitude());
    }

    public function testPositionCreatesFromArrayWithNull(): void
    {
        $position = Position::fromArray([
            'longitude' => null,
            'latitude' => null,
        ]);
        $this->assertNull($position->getLatitude());
        $this->assertNull($position->getLongitude());
    }

    public function testPositionCreatesFromLatLonWithNull(): void
    {
        $position = Position::fromLonLat(null, null);
        $this->assertNull($position->getLatitude());
        $this->assertNull($position->getLongitude());
    }

    public function testPositionIsInvalidIfLongitudeIsNull(): void
    {
        $position = Position::fromLonLat(null, 3);
        $this->assertFalse($position->isValid());
    }

    public function testPositionIsInvalidIfLatitudeIsNull(): void
    {
        $position = Position::fromLonLat(3, null);
        $this->assertFalse($position->isValid());
    }

    public function testPositionIsInvalidIfLongitudeLatitudeIsNull(): void
    {
        $position = Position::fromLonLat(null, null);
        $this->assertFalse($position->isValid());
    }

    public function testPositionIsValidIfLongitudeLatitudeNotNull(): void
    {
        $position = Position::fromLonLat(4, 2);
        $this->assertTrue($position->isValid());
    }

    /** @dataProvider stringCoordinatesDataProvider */
    public function testFromString(
        ?float $expectedLat,
        ?float $expectedLon,
        string $coordinates,
    ): void {
        $position = Position::fromString($coordinates);
        $this->assertEquals($expectedLat, $position->getLatitude());
        $this->assertEquals($expectedLon, $position->getLongitude());
    }

    public static function stringCoordinatesDataProvider(): array
    {
        return [
            'Test expects ok' => [
                'expectedLat' => 52.520008,
                'expectedLon' => 13.404954,
                'coordinates' => '52.520008,13.404954',
            ],
            'Test expects latitude invalid' => [
                'expectedLat' => null,
                'expectedLon' => 13.404954,
                'coordinates' => 'hello,13.404954',
            ],
            'Test expects longitude invalid' => [
                'expectedLat' => 52.520008,
                'expectedLon' => null,
                'coordinates' => '52.520008,sdf',
            ],
            'Test expects trimmed' => [
                'expectedLat' => 52.520008,
                'expectedLon' => 13.404954,
                'coordinates' => ' 52.520008, 13.404954 ',
            ],
            'Test expects both params are null if empty string provided' => [
                'expectedLat' => null,
                'expectedLon' => null,
                'coordinates' => '',
            ],
            'Test expects param is null if not provided' => [
                'expectedLat' => null,
                'expectedLon' => 13.404954,
                'coordinates' => ',13.404954',
            ],
            'Test expects longitude is null if one param provided' => [
                'expectedLat' => 52.520008,
                'expectedLon' => null,
                'coordinates' => '52.520008',
            ],
        ];
    }
}
