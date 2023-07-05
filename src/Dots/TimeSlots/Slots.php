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

/** @extends Collection<int, Slot> */
class Slots extends Collection
{
    public static function fromArray(array $data): static
    {
        $slots = array_map(
            fn (array $item) => Slot::fromArray($item),
            $data,
        );
        usort($slots, function (
            Slot $slot1,
            Slot $slot2,
        ) {
            return ($slot1->getStart() > $slot2->getStart()) ? 1 : -1;
        });

        return new static($slots);
    }

    public function findNearestSlot(int $timestamp, string $timezone): ?Slot
    {
        $time = Carbon::createFromTimestamp($timestamp, $timezone)->format('H:i');

        return $this->filter(
            fn (Slot $slot) => $slot->getEnd() > $time,
        )->first();
    }
}
