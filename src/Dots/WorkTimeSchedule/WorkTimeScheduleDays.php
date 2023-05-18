<?php
/**
 * Description of WorkTimeSheduleDays.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Dots\WorkTimeSchedule;

use Illuminate\Support\Collection;

/**
 * @extends Collection<int, WorkTimeScheduleDay>
 */
class WorkTimeScheduleDays extends Collection
{
    public static function fromArray(array $data): static
    {
        return new static(array_map(
            fn(array $item) => WorkTimeScheduleDay::fromArray($item),
            $data,
        ));
    }

    public function findNextActiveDayInWeekScope(int $dayId): ?WorkTimeScheduleDay
    {
        return $this->first(
            fn(WorkTimeScheduleDay $day) => $day->getId() > $dayId && $day->isStatusActive(),
        );
    }

    public function findDayById(int $id): ?WorkTimeScheduleDay
    {
        return $this->first(
            fn(WorkTimeScheduleDay $day) => $day->getId() === $id,
        );
    }
}