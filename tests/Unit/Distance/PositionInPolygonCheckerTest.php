<?php
/**
 * Description of PositionInPolygonCheckerTest.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Tests\Unit\Distance;

use Dots\Distance\PositionInPolygonChecker;
use Tests\Generators\Distance\PolygonGenerator;
use Tests\Generators\Distance\PositionGenerator;
use Tests\TestCase;

class PositionInPolygonCheckerTest extends TestCase
{
    private function getPositionInPolygonChecker(): PositionInPolygonChecker
    {
        return new PositionInPolygonChecker();
    }

    public function testCheckReturnsTrueIfInCoordinates(): void
    {
        $polygon = PolygonGenerator::chernihiv();
        $position = PositionGenerator::inPolygon();
        $inPolygon = $this->getPositionInPolygonChecker()->check($position, $polygon);

        $this->assertTrue($inPolygon);
    }

    public function testCheckReturnsFalseIfInCoordinates(): void
    {
        $polygon = PolygonGenerator::chernihiv();
        $position = PositionGenerator::outOfPolygon();
        $inPolygon = $this->getPositionInPolygonChecker()->check($position, $polygon);

        $this->assertFalse($inPolygon);
    }
}
