<?php
/**
 * Description of SlotDays.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Dots\TimeSlots;

use Illuminate\Support\Collection;

/** @method Day[] all() */

/** @extends  Collection<int, Day> */
class Days extends Collection
{
    public static function fromArray(array $data): static
    {
        return new static(
            array_map(
                fn (array $item) => Day::fromArray($item),
                $data,
            )
        );
    }

    public function findDay(int $id): ?Day
    {
        return $this->first(
            fn (Day $day) => $day->getId() === $id,
        );
    }
    public function findActiveDay(int $id): ?Day
    {
        return $this->first(
            fn (Day $day) => $day->getId() === $id && $day->isActive(),
        );
    }
}
