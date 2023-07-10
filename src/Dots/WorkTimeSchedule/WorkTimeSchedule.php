<?php
/**
 * Description of Slots.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Dots\WorkTimeSchedule;

use Carbon\Carbon;
use Dots\Data\DTO;

class WorkTimeSchedule extends DTO
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
        return $this->getDays()->isWorkingAtTime($timestamp, $this->getTimezone());
    }

    public function isWorkingDay(int $timestamp): bool
    {
        if ($this->isAnytime()) {
            return true;
        }

        return $this->getDays()->isWorkingDay($timestamp, $this->getTimezone());
    }

    public function getWorkTimeToday(): ?Day
    {
        return $this->getWorkTimeForWeekDay($this->getCurrentWeekDay());
    }

    public function getStartTimeToday(): ?int
    {
        return $this->getDays()->findFirstDaySlotStartTime(time(), $this->getTimezone());
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
        return $this->getDays()->getNearestStartTime($timestamp, $this->timezone);
    }

    public function getTimestampsSlotsByDays(int $startTime, int $daysCount = 14): array
    {
        return $this->getDays()->getTimestampsSlotsByDays($startTime, $this->getTimezone(), $daysCount);
    }

    private function getCurrentWeekDay(): int
    {
        return $this->getWeekDay(time());
    }

    private function getWeekDay(int $time): int
    {
        return $this->createDateFromTimestamp($time)->format('N') - 1;
    }

    private function findLastDaySlotEndTime(int $time): ?int
    {
        return $this->getDays()->findLastDaySlotEndTime($time, $this->getTimezone());
    }

    private function getWorkTimeForWeekDay(int $weekDay): ?Day
    {
        return $this->getDays()->findDay($weekDay);
    }

    private function createDateFromTimestamp(int $timestamp): Carbon
    {
        return Carbon::createFromTimestamp($timestamp, $this->getTimezone());
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
