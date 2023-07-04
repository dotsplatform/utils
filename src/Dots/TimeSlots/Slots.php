<?php
/**
 * Description of SlotTimes.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Dots\TimeSlots;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/** @method Slot[] all() */

/** @extends  Collection<int, Slot> */
class Slots extends Collection
{
    public static function fromArray(array $data): static
    {
        return new static(
            array_map(
                fn(array $item) => Slot::fromArray($item),
                $data,
            )
        );
    }

    public function findSlot(int $timestamp, string $timezone): ?Slot
    {
        $slots = $this->sortTimes();
        $time = Carbon::createFromTimestamp($timestamp, $timezone)->format('H:i');
        foreach ($slots as $slot) {
            if ($slot->getStart() < $time && $slot->getEnd() > $time) {
                return $slot;
            }
        }

        return null;
    }

    public function sortTimes(): static
    {
        return $this->sortBy([
            fn(Slot $slot1, Slot $slot2) => $slot1->getStart() > $slot2->getStart(),
        ]);
    }

}
