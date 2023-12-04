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
}
