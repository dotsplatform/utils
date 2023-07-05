<?php
/**
 * Description of Slots.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Dots\TimeSlots;

use Carbon\Carbon;
use Dots\Data\DTO;

class WorkTimeSchedule extends DTO
{
    private const DAYS_IN_WEEK = 7;

    protected bool $anytime = false;
    protected Days $days;
    protected string $timezone;

    public static function fromArray(array $data): static
    {
        $data['days'] = Days::fromArray($data['days'] ?? []);
        return parent::fromArray($data);
    }

    public function isWorkingNow(): bool
    {
        return $this->isWorkingAtTime(time());
    }

    public function isWorkingAtTime(int $timestamp): bool
    {
        if ($this->isAnytime()) {
            return true;
        }
        if ($this->isActiveDay($timestamp)) {
            if ($this->findFirstDaySlotStartTime($timestamp) <= $timestamp && $this->findLastDaySlotEndTime($timestamp) >= $timestamp) {
                return true;
            }
        }
        return false;
    }

    public function isWorkingDay(int $timestamp): bool
    {
        if ($this->isAnytime()) {
            return true;
        }
        if ($this->isActiveDay($timestamp) && $this->findLastDaySlotEndTime($timestamp) > $timestamp) {
            return true;
        }
        return false;
    }

    public function getWorkTimeToday(): ?Day
    {
        return $this->getWorkTimeForWeekDay($this->getCurrentWeekDay());
    }

    public function getStartTimeToday(): ?int
    {
        return $this->findFirstDaySlotStartTime(time());
    }

    public function getEndTimeToday(): ?int
    {
        return $this->findLastDaySlotEndTime(time());
    }

    public function getNearestStartTimeForNow(): ?int
    {
        return $this->getNearestStartTime(time());
    }

    /**
     * Calculates and returns nearest working date in Unix timestamp, for input timestamp.
     * If all days of the week are days off, will return null.
     */
    public function getNearestStartTime(int $timestamp): ?int
    {
        $nearestSlot = null;
        $time = Carbon::createFromTimestamp($timestamp, $this->timezone);
        for ($diffDays = 0; $diffDays < self::DAYS_IN_WEEK; $diffDays++) {
            $day = $this->getDays()->findActiveDay(abs($time->dayOfWeekIso - 1));
            if ($day) {
                $nearestSlot = $day->getSlots()->findNearestSlot($timestamp, $this->timezone);
                if ($nearestSlot) {
                    break;
                }
            }
            $time->addDay();
        }
        if (!$nearestSlot) {
            return null;
        }

        return Carbon::createFromTimestamp($timestamp, $this->timezone)
            ->addDays($diffDays)
            ->setTimeFromTimeString($nearestSlot->getStart())
            ->getTimestamp();
    }

    private function isActiveDay(int $timestamp): bool
    {
        if ($this->isAnytime()) {
            return true;
        }
        $day = $this->getWorkTimeForWeekDay($this->getWeekDay($timestamp));

        return (bool)$day?->isActive();
    }

    private function getCurrentWeekDay(): int
    {
        return $this->getWeekDay(time());
    }

    private function getWeekDay(int $time): int
    {
        return $this->createDateFromTimestamp($time)->format('N') - 1;
    }

    private function findFirstDaySlotStartTime(int $timestamp): ?int
    {
        $day = $this->findDayByTime($timestamp);
        $time = $day?->getSlots()->first()?->getStart();
        if (!$time) {
            return null;
        }
        return $this->generateTimestampForSpecifiedDayAndTime($timestamp, $time);
    }

    private function findLastDaySlotEndTime(int $timestamp): ?int
    {
        $day = $this->findDayByTime($timestamp);
        $time = $day?->getSlots()->last()?->getEnd();
        if (!$time) {
            return null;
        }
        return $this->generateTimestampForSpecifiedDayAndTime($timestamp, $time);
    }

    private function getWorkTimeForWeekDay(int $weekDay): ?Day
    {
        return $this->getDays()->findDay($weekDay);
    }

    private function generateTimestampForSpecifiedDayAndTime(int $timestamp, string $time): int
    {
        return $this->createDateFromTimestamp($timestamp)
            ->setTimeFromTimeString($time)
            ->getTimestamp();
    }

    private function createDateFromTimestamp(int $timestamp): Carbon
    {
        return Carbon::createFromTimestamp($timestamp, $this->getTimezone());
    }

    private function findDayByTime(int $timestamp): ?Day
    {
        return $this->getDays()->findDay(
            Carbon::createFromTimestamp($timestamp, $this->getTimezone())->dayOfWeekIso - 1,
        );
    }

    public function getDays(): Days
    {
        return $this->days;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function isAnytime(): bool
    {
        return $this->anytime;
    }
}
