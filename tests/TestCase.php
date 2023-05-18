<?php
/**
 * Description of TestCase.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Tests;

use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function generateUuid(): string
    {
        return Str::uuid()->__toString();
    }

    public function assertTimestampsAreEqualsInAccuracyToMinute(
        int $firstTimestamp,
        int $secondTimestamp,
    ): void {
        $this->assertTrue(abs($firstTimestamp - $secondTimestamp) < 60);
    }
}
