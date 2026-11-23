<?php

/**
 * Description of PositionRangeTest.php.
 *
 * Position::normalizeLongitude() wraps a finite longitude outside [-180, 180] into [-180, 180) (same point on the
 * globe); in-range and non-finite values are returned as is. isInRange() / isLatitudeInRange() /
 * isLongitudeInRange() accept finite latitude [-90, 90] and longitude [-180, 180], edges included.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Tests\Unit\Distance;

use Dots\Distance\Position;
use Tests\TestCase;

class PositionRangeTest extends TestCase
{
    private const float DELTA = 0.0000001;

    /**
     * @return array<string, array{float, float}>
     */
    public static function longitudesToNormalize(): array
    {
        return [
            'in range' => [30.4952, 30.4952],
            'zero' => [0.0, 0.0],
            'boundary 180 stays 180' => [180.0, 180.0],
            'boundary -180 stays -180' => [-180.0, -180.0],
            'Kyiv one turn east (production 31.08)' => [390.4952, 30.4952],
            'Kyiv one turn east, full precision' => [390.49515834326, 30.49515834326],
            'one turn west' => [-329.2132254147, 30.7867745853],
            'four turns west (production 23.11)' => [-1411.608294, 28.391706],
            'just above 180' => [180.5, -179.5],
            'just below -180' => [-180.5, 179.5],
            'tiny step above 180' => [180.000001, -179.999999],
            'tiny step below -180' => [-180.000001, 179.999999],
            'exactly one turn' => [360.0, 0.0],
            'exactly minus one turn' => [-360.0, 0.0],
            'exactly two turns' => [720.0, 0.0],
            'exactly 540 wraps to -180' => [540.0, -180.0],
            'exactly -540 wraps to -180' => [-540.0, -180.0],
            'many turns east' => [3630.5, 30.5],
        ];
    }

    /**
     * @dataProvider longitudesToNormalize
     */
    public function testNormalizeLongitude(float $longitude, float $expectedLongitude): void
    {
        $normalizedLongitude = Position::normalizeLongitude($longitude);

        $this->assertEqualsWithDelta($expectedLongitude, $normalizedLongitude, self::DELTA);
        $this->assertTrue(Position::isLongitudeInRange($normalizedLongitude));
    }

    public function testNormalizeLongitudeReturnsInRangeValueUnchanged(): void
    {
        $this->assertSame(30.4952, Position::normalizeLongitude(30.4952));
        $this->assertSame(180.0, Position::normalizeLongitude(180.0));
        $this->assertSame(-180.0, Position::normalizeLongitude(-180.0));
    }

    public function testNormalizeLongitudeReturnsNonFiniteValueUnchanged(): void
    {
        $this->assertSame(INF, Position::normalizeLongitude(INF));
        $this->assertSame(-INF, Position::normalizeLongitude(-INF));
        $this->assertNan(Position::normalizeLongitude(NAN));
    }

    /**
     * @return array<string, array{float, bool}>
     */
    public static function latitudes(): array
    {
        return [
            'zero' => [0.0, true],
            'Kyiv' => [50.4501, true],
            'boundary 90' => [90.0, true],
            'boundary -90' => [-90.0, true],
            'just above 90' => [90.000001, false],
            'just below -90' => [-90.000001, false],
            'far out of range' => [390.4952, false],
            'INF' => [INF, false],
            '-INF' => [-INF, false],
            'NAN' => [NAN, false],
        ];
    }

    /**
     * @dataProvider latitudes
     */
    public function testIsLatitudeInRange(float $latitude, bool $expectedInRange): void
    {
        $this->assertSame($expectedInRange, Position::isLatitudeInRange($latitude));
    }

    /**
     * @return array<string, array{float, bool}>
     */
    public static function longitudes(): array
    {
        return [
            'zero' => [0.0, true],
            'Kyiv' => [30.4952, true],
            'boundary 180' => [180.0, true],
            'boundary -180' => [-180.0, true],
            'just above 180' => [180.000001, false],
            'just below -180' => [-180.000001, false],
            'production 31.08' => [390.4952, false],
            'production 23.11' => [-1411.608294, false],
            'INF' => [INF, false],
            '-INF' => [-INF, false],
            'NAN' => [NAN, false],
        ];
    }

    /**
     * @dataProvider longitudes
     */
    public function testIsLongitudeInRange(float $longitude, bool $expectedInRange): void
    {
        $this->assertSame($expectedInRange, Position::isLongitudeInRange($longitude));
    }

    /**
     * @return array<string, array{?float, ?float, bool}>
     */
    public static function positions(): array
    {
        return [
            'Kyiv' => [50.4501, 30.5234, true],
            'zero' => [0.0, 0.0, true],
            'north-east corner' => [90.0, 180.0, true],
            'south-west corner' => [-90.0, -180.0, true],
            'latitude null' => [null, 30.5234, false],
            'longitude null' => [50.4501, null, false],
            'both null' => [null, null, false],
            'latitude just above 90' => [90.000001, 30.5234, false],
            'latitude just below -90' => [-90.000001, 30.5234, false],
            'longitude just above 180' => [50.4501, 180.000001, false],
            'longitude just below -180' => [50.4501, -180.000001, false],
            'longitude production 31.08' => [50.4501, 390.4952, false],
            'longitude production 23.11' => [50.4501, -1411.608294, false],
            'latitude INF' => [INF, 30.5234, false],
            'longitude NAN' => [50.4501, NAN, false],
        ];
    }

    /**
     * @dataProvider positions
     */
    public function testIsInRange(?float $latitude, ?float $longitude, bool $expectedInRange): void
    {
        $position = Position::fromLonLat($longitude, $latitude);

        $this->assertSame($expectedInRange, $position->isInRange());
    }

    public function testIsValidStillOnlyChecksForNull(): void
    {
        $outOfRangePosition = Position::fromLonLat(390.4952, 50.4501);

        $this->assertTrue($outOfRangePosition->isValid());
        $this->assertFalse($outOfRangePosition->isInRange());
    }

    public function testConstructionDoesNotWrapLongitude(): void
    {
        $fromLonLat = Position::fromLonLat(390.4952, 50.4501);
        $fromArray = Position::fromArray(['latitude' => 50.4501, 'longitude' => -1411.608294]);

        $this->assertSame(390.4952, $fromLonLat->getLongitude());
        $this->assertSame(-1411.608294, $fromArray->getLongitude());
    }
}
