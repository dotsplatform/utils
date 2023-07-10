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
    private const DAYS_IN_WEEK = 7;

    public static function fromArray(array $data): static
    {
        return new static(
            array_map(
                fn (array $item) => Day::fromArray($item),
                $data,
            )
        );
    }

    public function getTimestampsSlotsByDays(int $startTime, string $timezone, int $daysCount = 14): array
    {
        $slots = [];
        $day = $this->createDateFromTimestamp($startTime, $timezone);
        while ($daysCount) {
            $daySlots = $this->getDaySlotsTimestamps($day, $timezone, $startTime);
            if ($daySlots) {
                $slots[] = $daySlots;
            }

            $day->addDay();
            $daysCount--;
        }

        return $slots;
    }

    public function getDaySlotsTimestamps(Carbon $day, string $timezone, int $startTime): array
    {
        $daySlots = $this->getNearestDaySlots($day, $startTime, $timezone);
        if ($daySlots->isEmpty()) {
            return [];
        }

        return [
            'date' => (clone $day)->startOfDay()->getTimestamp(),
            'times' => $daySlots->getDaySlotsTimestamps($day),
        ];
    }

    public function getNearestStartTime(int $timestamp, string $timezone): ?int
    {
        $nearestSlot = null;
        $day = $this->createDateFromTimestamp($timestamp, $timezone);
        for ($diffDays = 0; $diffDays < self::DAYS_IN_WEEK; $diffDays++) {
            $nearestSlot = $this->getNearestDaySlots($day, $timestamp, $timezone)->first();
            if ($nearestSlot) {
                break;
            }
            $day->addDay();
        }

        if (!$nearestSlot) {
            return null;
        }

        return $this->createDateFromTimestamp($timestamp, $timezone)
            ->addDays($diffDays)
            ->setTimeFromTimeString($nearestSlot->getStart())
            ->getTimestamp();
    }

    public function getNearestDaySlots(Carbon $day, int $startTime, string $timezone): Slots
    {
        $weekDay = $this->findActiveDayForTime($day);
        if (!$weekDay) {
            return Slots::empty();
        }
        return $weekDay->getNearestSlots($startTime, $timezone);
    }

    public function isWorkingDay(int $timestamp, string $timezone): bool
    {
        $day = $this->findDayForTimestamp($timestamp, $timezone);
        if (!$day?->isActive()) {
            return false;
        }

        return $this->findLastDaySlotEndTime($timestamp, $timezone) > $timestamp;
    }

    public function isWorkingAtTime(int $timestamp, string $timezone): bool
    {
        $day = $this->findDayForTimestamp($timestamp, $timezone);
        if (!$day?->isActive()) {
            return false;
        }

        return $this->findFirstDaySlotStartTime($timestamp, $timezone) <= $timestamp &&
            $this->findLastDaySlotEndTime($timestamp, $timezone) >= $timestamp;
    }

    public function findFirstDaySlotStartTime(int $timestamp, string $timezone): ?int
    {
        $dayTime = $this->createDateFromTimestamp($timestamp, $timezone);
        $day = $this->findDayForTime($dayTime);
        return $day?->getSlots()->first()?->getDayStartTimeTimestamp($dayTime);
    }

    public function findLastDaySlotEndTime(int $timestamp, string $timezone): ?int
    {
        $dayTime = $this->createDateFromTimestamp($timestamp, $timezone);
        $day = $this->findDayForTime($dayTime);
        return $day?->getSlots()->last()?->getDayEndTimeTimestamp($dayTime);
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

    public function findDayForTimestamp(int $timestamp, string $timezone): ?Day
    {
        $dayTime = $this->createDateFromTimestamp($timestamp, $timezone);
        return $this->findDayForTime($dayTime);
    }

    public function findDayForTime(Carbon $time): ?Day
    {
        return $this->findDay($time->dayOfWeekIso - 1);
    }

    public function findDay(int $id): ?Day
    {
        return $this->first(
            fn (Day $day) => $day->getId() === $id,
        );
    }

    private function createDateFromTimestamp(int $timestamp, string $timezone): Carbon
    {
        return Carbon::createFromTimestamp($timestamp, $timezone);
    }
}
