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
    protected bool $alwaysOn = false;
    protected array $schedule = [];
    protected ?string $timezone = null;

    public function isWorkingNow(): bool
    {
        return $this->isWorkingAtTime(time());
    }

    public function isWorkingAtTime(int $timestamp): bool
    {
        if ($this->isAlwaysOn()) {
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
        if ($this->isAlwaysOn()) {
            return true;
        }
        if ($this->isActiveDay($timestamp) && $this->getEndTime($timestamp) > $timestamp) {
            return true;
        }
        return false;
    }

    private function isActiveDay(int $timestamp): bool
    {
        if ($this->isAlwaysOn()) {
            return true;
        }
        if (! $this->isEmptyWorkTime()) {
            $workTime = $this->getWorkTimeForWeekDay($this->getWeekDay($timestamp));
            if (empty($workTime['start']) || empty($workTime['end'])) {
                return false;
            }
            return (bool)$workTime['status'];
        }
        return false;
    }

    public function isEmptyWorkTime(): bool
    {
        return empty($this->getSchedule());
    }

    public function getWorkTimeToday(): ?array
    {
        $workTime = $this->getSchedule();

        return $workTime[$this->getCurrentWeekDay()] ?? null;
    }

    public function getStartTimeToday(): int|bool
    {
        return $this->getStartTime(time());
    }

    public function getEndTimeToday(): int|bool
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
     *
     * @param int $timestamp
     * @return int|null
     */
    public function getNearestStartTime(int $timestamp): ?int
    {
        if ($this->isWorkingAtTime($timestamp)) {
            $workingSchedule = $this->getWorkTimeForWeekDay($this->getWeekDayByTimestamp($timestamp));
            if (!is_array($workingSchedule)) {
                return null;
            }
            $date = $this->createDateFromTimestamp($timestamp)->format('Y-m-d');
            $dateTime = $date . $workingSchedule['start'];
            try {
                return $this->createDateFromFormat($dateTime)->getTimestamp();
            } catch (Exception $e) {
                return null;
            }
        }

        $nextWorkingWeekDay = $this->getNextWorkingWeekDay($timestamp);

        if (!is_int($nextWorkingWeekDay)) {
            return null;
        }

        $workingScheduleForNextWorkingWeekDay = $this->getWorkTimeForWeekDay($nextWorkingWeekDay);
        if (!is_array($workingScheduleForNextWorkingWeekDay)) {
            return null;
        }
        $nextWorkingDate = $this->getNextWorkingDate($nextWorkingWeekDay, $timestamp);
        $nextWorkingDateTime = $nextWorkingDate . $workingScheduleForNextWorkingWeekDay['start'];

        try {
            return $this->createDateFromFormat($nextWorkingDateTime)->getTimestamp();
        } catch (Exception $e) {
        }
        return null;
    }

    /**
     * Gets, calculates and returns first nearest working week day number (0-6) for input timestamp.
     * Or returns false, if all days are day off.
     *
     */
    private function getNextWorkingWeekDay(int $timestamp): int|bool
    {
        $currentWeekDay = $this->getWeekDay($timestamp);
        $workTimeArray = $this->getSchedule();
        $i = 0;
        while ($i < count($workTimeArray)) {
            if ($workTimeArray[$currentWeekDay]['status']) {
                return $currentWeekDay;
            }
            $currentWeekDay++;
            if ($currentWeekDay == count($workTimeArray)) {
                $currentWeekDay = 0;
            }
            $i++;
        }

        return false;
    }

    /**
     * Get date of the nearest working week day by its number (0-6) for input timestamp.
     *
     * @param $nextWorkingWeekDay
     * @param $timestamp
     * @return string
     */
    private function getNextWorkingDate(int $nextWorkingWeekDay, int $timestamp): string
    {
        $nextWorkingWeekDay++;
        $today = $this->createDateFromTimestamp($timestamp);
        $todayWeekDay = (int)$today->format('N');
        $diffDays = $nextWorkingWeekDay - $todayWeekDay;
        if ($nextWorkingWeekDay < $todayWeekDay) {
            $diffDays = 7 - $todayWeekDay + $nextWorkingWeekDay;
        }
        return $today->addDays($diffDays)->format('Y-m-d');
    }

    private function getCurrentWeekDay(): int
    {
        return $this->getWeekDay(time());
    }

    private function getWeekDay(int $time): int
    {
        return $this->createDateFromTimestamp($time)->format('N') - 1;
    }

    /**
     * There is a bug, we expect that $workTime has all days from weekDay, and each day has corresponding array key to
     * his id of day.
     */
    private function getWorkTimeForWeekDay(int $weekDay): array|bool
    {
        $workTime = $this->getSchedule();
        if (! $workTime) {
            return false;
        }
        foreach ($workTime as $key => $workDay) {
            if ($workDay['status'] == 0) {
                unset($workTime[$key]);
            }
        }
        return ! empty($workTime[$weekDay]) ? $workTime[$weekDay] : false;
    }

    public function getWorkTimeWeekDayByTime(int $timestamp): array|bool
    {
        return $this->getWorkTimeForWeekDay($this->getWeekDay($timestamp));
    }

    private function getStartTime(int $timestamp): int|bool
    {
        $workTime = $this->getWorkTimeForWeekDay($this->getWeekDay($timestamp));
        if (!is_array($workTime)) {
            return false;
        }
        return $this->getTime($timestamp, $workTime['start']);
    }

    private function getEndTime(int $timestamp): int|bool
    {
        $workTime = $this->getWorkTimeForWeekDay($this->getWeekDay($timestamp));
        if (!is_array($workTime)) {
            return false;
        }
        return $this->getTime($timestamp, $workTime['end']);
    }

    private function getTime(int $timestamp, string $time): int
    {
        if (! $time) {
            return $timestamp;
        }
        $day = $this->createDateFromTimestamp($timestamp)->format('Y-m-d');
        $fullDay = $day . $time;
        try {
            return $this->createDateFromFormat($fullDay, 'Y-m-d H:i')->getTimestamp();
        } catch (Exception $e) {
            return $timestamp;
        }
    }

    private function getWeekDayByTimestamp(int $timestamp): int
    {
        return $this->createDateFromTimestamp($timestamp)->format('N') - 1;
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

    public function isAlwaysOn(): bool
    {
        return $this->alwaysOn;
    }

    public function getSchedule(): array
    {
        return $this->schedule;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }
}