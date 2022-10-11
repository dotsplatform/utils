<?php
/**
 * Description of PositionTest.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Tests\Unit\Distance\DTO;

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
}
