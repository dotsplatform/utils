<?php

/**
 * Description of CoordinateRulesTest.php.
 *
 * LatitudeRule / LongitudeRule accept a number or numeric string, finite, inside latitude [-90, 90] / longitude
 * [-180, 180], edges included, and never change the value.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Tests\Unit\Distance\Rules;

use Dots\Distance\Rules\LatitudeRule;
use Dots\Distance\Rules\LongitudeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Tests\TestCase;

class CoordinateRulesTest extends TestCase
{
    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function latitudeValues(): array
    {
        return [
            'float' => [50.4501, true],
            'numeric string' => ['50.4501', true],
            'integer' => [50, true],
            'zero' => [0, true],
            'boundary 90' => [90, true],
            'boundary -90' => [-90, true],
            'boundary 90 as string' => ['90', true],
            'just above 90' => [90.000001, false],
            'just below -90' => [-90.000001, false],
            'just above 90 as string' => ['90.000001', false],
            'not numeric' => ['abc', false],
            'empty string' => ['', false],
            'array' => [[50.4501], false],
            'boolean' => [true, false],
            'INF' => [INF, false],
            'NAN' => [NAN, false],
            'INF as string' => ['INF', false],
        ];
    }

    /**
     * @dataProvider latitudeValues
     */
    public function testLatitudeRule(mixed $value, bool $expectedPasses): void
    {
        $failMessages = $this->runRule(new LatitudeRule(), 'latitude', $value);

        $this->assertSame($expectedPasses, $failMessages === []);
    }

    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function longitudeValues(): array
    {
        return [
            'float' => [30.4952, true],
            'numeric string' => ['30.4952', true],
            'integer' => [30, true],
            'boundary 180' => [180, true],
            'boundary -180' => [-180, true],
            'boundary -180 as string' => ['-180', true],
            'just above 180' => [180.000001, false],
            'just below -180' => [-180.000001, false],
            'production 31.08 (not wrapped by the rule)' => [390.4952, false],
            'production 23.11 (not wrapped by the rule)' => ['-1411.608294', false],
            'not numeric' => ['abc', false],
            'empty string' => ['', false],
            'array' => [[30.4952], false],
            'INF' => [INF, false],
            '-INF' => [-INF, false],
            'NAN' => [NAN, false],
            'NAN as string' => ['NAN', false],
        ];
    }

    /**
     * @dataProvider longitudeValues
     */
    public function testLongitudeRule(mixed $value, bool $expectedPasses): void
    {
        $failMessages = $this->runRule(new LongitudeRule(), 'longitude', $value);

        $this->assertSame($expectedPasses, $failMessages === []);
    }

    public function testLatitudeRuleMessage(): void
    {
        $failMessages = $this->runRule(new LatitudeRule(), 'latitude', 91);

        $this->assertSame(['The :attribute field must be a latitude between -90 and 90.'], $failMessages);
    }

    public function testLongitudeRuleMessage(): void
    {
        $failMessages = $this->runRule(new LongitudeRule(), 'longitude', 'abc');

        $this->assertSame(['The :attribute field must be a longitude between -180 and 180.'], $failMessages);
    }

    /**
     * @return list<string>
     */
    private function runRule(ValidationRule $rule, string $attribute, mixed $value): array
    {
        $failMessages = [];
        $fail = function (string $message) use (&$failMessages): void {
            $failMessages[] = $message;
        };

        $rule->validate($attribute, $value, $fail);

        return $failMessages;
    }
}
