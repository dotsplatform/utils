<?php

/**
 * Description of WorkTimeSchedule.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Dots\WorkTimeSchedule;

use Dots\Data\Entity;
use Exception;
use Illuminate\Support\Carbon;
use RuntimeException;

class WorkTimeSchedule extends Entity
{
    protected bool $anytime = false;
    protected WorkTimeScheduleDays $workTimeScheduleDays;
    protected string $timezone;

    public static function fromArray(array $data): static
    {
        $data['workTimeScheduleDays'] = WorkTimeScheduleDays::fromArray($data['workTimeScheduleDays'] ?? []);
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
            if ($this->getStartTime($timestamp) <= $timestamp && $this->getEndTime($timestamp) >= $timestamp) {
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
        if ($this->isActiveDay($timestamp) && $this->getEndTime($timestamp) > $timestamp) {
            return true;
        }
        return false;
    }

    public function getWorkTimeToday(): ?WorkTimeScheduleDay
    {
        return $this->getWorkTimeForWeekDay($this->getCurrentWeekDay());
    }

    public function getStartTimeToday(): ?int
    {
        return $this->getStartTime(time());
    }

    public function getEndTimeToday(): ?int
    {
        return $this->getEndTime(time());
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
            $day = $this->getWorkTimeForWeekDay($this->getWeekDay($timestamp));
            if (! $day) {
                return null;
            }

            return $this->generateTimestampForSpecifiedDayAndTime($timestamp, $day->getStartTime()->getTime());
        }

        $nextWorkingWeekDay = $this->getNextWorkingWeekDay($timestamp);
        if (! $nextWorkingWeekDay) {
            return null;
        }

        $nextWorkingDayTimestamp = $this->getNextWorkingDayTimestamp($nextWorkingWeekDay->getId(), $timestamp);

        return $this->generateTimestampForSpecifiedDayAndTime(
            $nextWorkingDayTimestamp,
            $nextWorkingWeekDay->getStartTime()->getTime(),
        );
    }

    private function isActiveDay(int $timestamp): bool
    {
        if ($this->isAnytime()) {
            return true;
        }
        $day = $this->getWorkTimeForWeekDay($this->getWeekDay($timestamp));

        return (bool)$day?->isStatusActive();
    }

    private function getNextWorkingWeekDay(int $timestamp): ?WorkTimeScheduleDay
    {
        return $this->getWorkTimeScheduleDays()->findNextActiveDayInWeekScope($this->getWeekDay($timestamp));
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

    private function getStartTime(int $timestamp): ?int
    {
        $day = $this->getWorkTimeForWeekDay($this->getWeekDay($timestamp));
        if (! $day) {
            return null;
        }
        return $this->generateTimestampForSpecifiedDayAndTime($timestamp, $day->getStartTime()->getTime());
    }

    private function getEndTime(int $timestamp): ?int
    {
        $day = $this->getWorkTimeForWeekDay($this->getWeekDay($timestamp));
        if (! $day) {
            return null;
        }
        return $this->generateTimestampForSpecifiedDayAndTime($timestamp, $day->getEndTime()->getTime());
    }

    private function getWorkTimeForWeekDay(int $weekDay): ?WorkTimeScheduleDay
    {
        return $this->getWorkTimeScheduleDays()->findDayById($weekDay);
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
        if (! $date instanceof Carbon) {
            throw new RuntimeException("Unable resolve date format {$date}");
        }
        return $date;
    }

    public function isAnytime(): bool
    {
        return $this->anytime;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function getWorkTimeScheduleDays(): WorkTimeScheduleDays
    {
        return $this->workTimeScheduleDays;
    }
}