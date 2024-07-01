<?php
/**
 * Description of Period.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dots\Utils;

use Dots\Data\Entity;

class Period extends Entity
{
    public const MONTH = 'month';
    public const WEEK = 'week';
    public const DAY = 'day';
    public const HOUR = 'hour';
    public const MINUTE = 'min';

    protected ?string $type;
    protected int $value = 0;

    public function getPeriodSeconds(): int
    {
        return match ($this->type) {
            self::MONTH => 30 * 24 * 60 * 60 * $this->value,
            self::WEEK => 7 * 24 * 60 * 60 * $this->value,
            self::DAY => 24 * 60 * 60 * $this->value,
            self::HOUR => 60 * 60 * $this->value,
            self::MINUTE => 60 * $this->value,
            default => 0,
        };
    }

    public static function hours(int $value): static
    {
        return static::fromArray([
            'type' => self::HOUR,
            'value' => $value,
        ]);
    }

    public function getValue(): int
    {
        return $this->value;
    }
}