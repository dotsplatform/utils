<?php
/**
 * Description of PositionGenerator.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Tests\Generators\Distance;

use Dots\Distance\Position;

class PositionGenerator
{
    public const LATITUDE = 51.5178528;
    public const LONGITUDE = 31.2809114;

    public static function inPolygon(): Position
    {
        return Position::fromLonLat(
            self::LONGITUDE,
            self::LATITUDE
        );
    }

    public static function outOfPolygon(): Position
    {
        return Position::fromLonLat(1, 2);
    }
}
