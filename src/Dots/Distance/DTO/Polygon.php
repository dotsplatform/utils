<?php
/**
 * Description of Polygon.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Dots\Distance\DTO;

use Dots\Data\DTO;
use Dots\Distance\Position;

/**
 * Class Polygon.
 *
 * @template Position
 */
class Polygon extends DTO
{
    /** @var Position[] */
    protected array $positions;

    public static function fromPositionsArray(array $positions): static
    {
        $items = array_map(
            fn ($item) => Position::fromArray($item),
            $positions,
        );

        return static::fromPositions($items);
    }

    public static function fromPositions(array $positions): static
    {
        return static::fromArray([
            'positions' => $positions,
        ]);
    }

    public function isEmpty(): bool
    {
        return empty($this->positions);
    }

    public function getPositions(): array
    {
        return $this->positions;
    }

    public function getPositionsArray(): array
    {
        return array_map(
            fn (Position $item) => $item->toArray(),
            $this->getPositions(),
        );
    }
}
