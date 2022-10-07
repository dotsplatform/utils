<?php
/**
 * Description of DistanceBetweenCoordinatesResolver.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Dots\Distance;

class DistanceBetweenCoordinatesResolver
{
    public const EARTH_RADIUS_METERS = 6371000;

    public function resolveBetweenPositions(
        Position $positionFrom,
        Position $positionTo
    ): float {
        return $this->resolve(
            $positionFrom->getLatitude() ?? 0,
            $positionFrom->getLongitude() ?? 0,
            $positionTo->getLatitude() ?? 0,
            $positionTo->getLongitude() ?? 0,
        );
    }

    /**
     * Calculates the great-circle distance between two points, with
     * the Vincenty formula.
     *
     * @param  float  $latitudeFrom  Latitude of start point in [deg decimal]
     * @param  float  $longitudeFrom  Longitude of start point in [deg decimal]
     * @param  float  $latitudeTo  Latitude of target point in [deg decimal]
     * @param  float  $longitudeTo  Longitude of target point in [deg decimal]
     * @return float Distance between points in [m] (same as earthRadius)
     */
    public function resolve(
        float $latitudeFrom,
        float $longitudeFrom,
        float $latitudeTo,
        float $longitudeTo
    ): float {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
            pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);

        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);

        return $angle * self::EARTH_RADIUS_METERS;
    }
}
