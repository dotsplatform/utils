<?php
/**
 * Description of Slots.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Dots\TimeSlots;

use Carbon\Carbon;
use Dots\Data\DTO;
use Exception;
use RuntimeException;

class TimeSlots extends DTO
{
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
            if ($this->findFirstDayStartTime($timestamp) <= $timestamp && $this->findFirstDayEndTime($timestamp) >= $timestamp) {
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
        if ($this->isActiveDay($timestamp) && $this->findFirstDayEndTime($timestamp) > $timestamp) {
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
        return $this->findFirstDayStartTime(time());
    }

    public function getEndTimeToday(): ?int
    {
        return $this->findFirstDayEndTime(time());
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
        if ($this->isWorkingAtTime($timestamp)) {
            $slot = $this->findFirstDaySlot($timestamp);
            if (!$slot) {
                return null;
            }

            return $this->generateTimestampForSpecifiedDayAndTime(
                $timestamp,
                $slot->getStart(),
            );
        }

        $nextWorkingWeekDay = $this->getDays()->getNextWorkingWeekDay($this->getWeekDay($timestamp));
        if (!$nextWorkingWeekDay) {
            return null;
        }
        $timeSlot = $nextWorkingWeekDay->getSlots()->first();
        if (!$timeSlot) {
            return null;
        }

        $nextWorkingDayTimestamp = $this->getNextWorkingDayTimestamp($nextWorkingWeekDay->getId(), $timestamp);

        return $this->generateTimestampForSpecifiedDayAndTime(
            $nextWorkingDayTimestamp,
            $timeSlot->getStart(),
        );
    }

    private function isActiveDay(int $timestamp): bool
    {
        if ($this->isAnytime()) {
            return true;
        }
        $day = $this->getWorkTimeForWeekDay($this->getWeekDay($timestamp));

        return (bool)$day?->isActive();
    }

    private function getNextWorkingDayTimestamp(int $nextWorkingWeekDay, int $timestamp): int
    {
        $nextWorkingWeekDay++;
        $today = $this->createDateFromTimestamp($timestamp);
        $todayWeekDay = (int)$today->format('N');
        $diffDays = $nextWorkingWeekDay - $todayWeekDay;
        if ($nextWorkingWeekDay < $todayWeekDay) {
            $diffDays = 7 - $todayWeekDay + $nextWorkingWeekDay;
        }
        return $today->addDays($diffDays)->startOfDay()->getTimestamp();
    }

    private function getCurrentWeekDay(): int
    {
        return $this->getWeekDay(time());
    }

    private function getWeekDay(int $time): int
    {
        return $this->createDateFromTimestamp($time)->format('N') - 1;
    }

    private function findFirstDayStartTime(int $timestamp): ?int
    {
        $slot = $this->findFirstDaySlot($timestamp);
        if (!$slot) {
            return null;
        }
        return $this->generateTimestampForSpecifiedDayAndTime($timestamp, $slot->getStart());
    }

    private function findFirstDayEndTime(int $timestamp): ?int
    {
        $slot = $this->findFirstDaySlot($timestamp);
        if (!$slot) {
            return null;
        }
        return $this->generateTimestampForSpecifiedDayAndTime($timestamp, $slot->getEnd());
    }

    private function getWorkTimeForWeekDay(int $weekDay): ?Day
    {
        return $this->getDays()->findDay($weekDay);
    }

    private function generateTimestampForSpecifiedDayAndTime(int $timestamp, string $time): int
    {
        $day = $this->createDateFromTimestamp($timestamp)->format('Y-m-d');
        $fullDay = $day . ' ' . $time;
        try {
            return $this->createDateFromFormat($fullDay, 'Y-m-d H:i')->getTimestamp();
        } catch (Exception) {
            return $timestamp;
        }
    }

    private function createDateFromTimestamp(int $timestamp): Carbon
    {
        return Carbon::createFromTimestamp($timestamp, $this->getTimezone());
    }

    private function createDateFromFormat(string $date, string $format = 'Y-m-d H:i'): Carbon
    {
        $date = Carbon::createFromFormat($format, $date, $this->getTimezone());
        if (!$date instanceof Carbon) {
            throw new RuntimeException("Unable resolve date format {$date}");
        }
        return $date;
    }

    public function findFirstDaySlot(int $timestamp): ?Slot
    {
        $day = $this->findDayByTime($timestamp);
        if (!$day) {
            return null;
        }
        return $day->getSortedSlots()->first();
    }

    private function findDayByTime(int $timestamp): ?Day
    {
        return $this->getDays()->findDay(
            Carbon::createFromTimestamp($timestamp, $this->getTimezone())->dayOfWeek - 1,
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
