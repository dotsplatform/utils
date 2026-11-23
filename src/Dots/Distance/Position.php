<?php
/**
 * Description of Position.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Dots\Distance;

use Dots\Data\DTO;

class Position extends DTO
{
    public const float MIN_LATITUDE = -90.0;

    public const float MAX_LATITUDE = 90.0;

    public const float MIN_LONGITUDE = -180.0;

    public const float MAX_LONGITUDE = 180.0;

    private const float FULL_TURN_DEGREES = 360.0;

    protected ?float $latitude = null;
    protected ?float $longitude = null;

    public static function fromString(string $coordinates): static
    {
        $coordinates = explode(',', $coordinates);
        $latitude = $coordinates[0] ?? '';
        $longitude = $coordinates[1] ?? '';
        $latitude = trim($latitude);
        $longitude = trim($longitude);
        if (!is_numeric($latitude)) {
            $latitude = null;
        }

        if (!is_numeric($longitude)) {
            $longitude = null;
        }

        return static::fromArray([
            'latitude' => $latitude ? (float)$latitude : null,
            'longitude' => $longitude ? (float)$longitude : null,
        ]);
    }

    /**
     * Map widgets report the centre of a map that was panned across the antimeridian without wrapping it,
     * for example 390.4952 for Kyiv (30.4952). It is the same point on the globe as the wrapped value.
     *
     * A longitude inside [-180, 180] is returned as is (both edges included).
     * A finite longitude outside it is wrapped into [-180, 180): 540 becomes -180, never 180.
     * A non-finite value (INF, NAN) is returned as is, so the range validation rejects it.
     */
    public static function normalizeLongitude(float $longitude): float
    {
        if (! is_finite($longitude)) {
            return $longitude;
        }

        if (self::isLongitudeInRange($longitude)) {
            return $longitude;
        }

        return self::wrapLongitude($longitude);
    }

    /**
     * True for a finite latitude in [-90, 90], edges included.
     */
    public static function isLatitudeInRange(float $latitude): bool
    {
        if (! is_finite($latitude)) {
            return false;
        }

        if ($latitude < self::MIN_LATITUDE) {
            return false;
        }

        return $latitude <= self::MAX_LATITUDE;
    }

    /**
     * True for a finite longitude in [-180, 180], edges included.
     */
    public static function isLongitudeInRange(float $longitude): bool
    {
        if (! is_finite($longitude)) {
            return false;
        }

        if ($longitude < self::MIN_LONGITUDE) {
            return false;
        }

        return $longitude <= self::MAX_LONGITUDE;
    }

    public static function fromLonLat(?float $longitude, ?float $latitude): static
    {
        return static::fromArray([
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    public function isValid(): bool
    {
        return !is_null($this->getLatitude()) && !is_null($this->getLongitude());
    }

    /**
     * Unlike isValid(), also requires both values to be finite and inside latitude [-90, 90] / longitude [-180, 180],
     * the range MySQL spatial functions (ST_GeomFromText) accept.
     */
    public function isInRange(): bool
    {
        $latitude = $this->getLatitude();
        $longitude = $this->getLongitude();
        if (is_null($latitude) || is_null($longitude)) {
            return false;
        }

        if (! self::isLatitudeInRange($latitude)) {
            return false;
        }

        return self::isLongitudeInRange($longitude);
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function __toString(): string
    {
        if (!$this->latitude || !$this->longitude) {
            return '';
        }

        return "$this->latitude,$this->longitude";
    }

    private static function wrapLongitude(float $longitude): float
    {
        $degreesFromMinLongitude = fmod($longitude - self::MIN_LONGITUDE, self::FULL_TURN_DEGREES);
        if ($degreesFromMinLongitude < 0) {
            $degreesFromMinLongitude += self::FULL_TURN_DEGREES;
        }

        return $degreesFromMinLongitude + self::MIN_LONGITUDE;
    }
}
