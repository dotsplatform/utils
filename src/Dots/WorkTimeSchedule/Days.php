<?php
/**
 * Description of SlotDays.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Dots\WorkTimeSchedule;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/** @method Day[] all() */

/** @extends Collection<int, Day> */
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

    public function getNearestDaySlots(Carbon $day, int $startTime, string $timezone): Slots
    {
        $weekDay = $this->findActiveDayForTime($day);
        if (!$weekDay) {
            return Slots::empty();
        }
        return $weekDay->getNearestSlots($startTime, $timezone);
    }

    public function findActiveDayForTime(Carbon $time): ?Day
    {
        return $this->findActiveDay($time->dayOfWeekIso - 1);
    }

    public function findActiveDay(int $id): ?Day
    {
        return $this->first(
            fn (Day $day) => $day->getId() === $id && $day->isActive(),
        );
    }

    public function findDayByTime(int $timestamp, string $timezone): ?Day
    {
        return $this->findDay(
            Carbon::createFromTimestamp($timestamp, $timezone)->dayOfWeekIso - 1,
        );
    }

    public function findDay(int $id): ?Day
    {
        return $this->first(
            fn (Day $day) => $day->getId() === $id,
        );
    }
}
