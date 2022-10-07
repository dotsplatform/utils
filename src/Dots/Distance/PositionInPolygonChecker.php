<?php
/**
 * Description of CoordinatesInPolygonChecker.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Dots\Distance;

use Dots\Distance\DTO\Polygon;

class PositionInPolygonChecker
{
    public function check(Position $position, Polygon $polygon): bool
    {
        $coordinatesX = (float) $position->getLatitude();
        $coordinatesY = (float) $position->getLongitude();
        $inside = false;
        $positions = $polygon->getPositions();
        for ($i = 0, $j = count($positions) - 1; $i < count($positions); $j = $i++) {
            $xi = (float) $positions[$i]->getLatitude();
            $yi = (float) $positions[$i]->getLongitude();
            $xj = (float) $positions[$j]->getLatitude();
            $yj = (float) $positions[$j]->getLongitude();
            $intersect = (($yi > $coordinatesY) != ($yj > $coordinatesY))
                && ($coordinatesX < ($xj - $xi) * ($coordinatesY - $yi) / ($yj - $yi) + $xi);
            if ($intersect) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }
}
