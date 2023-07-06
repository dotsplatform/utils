<?php
/**
 * Description of SlotTimes.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Dots\WorkTimeSchedule;

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
        return $this->getNearestSlots($timestamp, $timezone)->first();
    }

    public function getNearestSlots(int $timestamp, string $timezone): static
    {
        $time = Carbon::createFromTimestamp($timestamp, $timezone)->format('H:i');
        return $this->filter(
            fn (Slot $slot) => ($slot->getEnd() > $time) && ($slot->getStart() > $time),
        );
    }

    public function getDaySlotsTimestamps(Carbon $day): array
    {
        $slotsTimestamps = $this->map(
            fn (Slot $slot) => [
                'start' => (clone $day)->setTimeFromTimeString($slot->getStart())->getTimestamp(),
                'end' => (clone $day)->setTimeFromTimeString($slot->getEnd())->getTimestamp(),
            ],
        )->toArray();

        return array_values($slotsTimestamps);
    }
}
