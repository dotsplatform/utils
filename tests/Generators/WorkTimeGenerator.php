<?php
/**
 * Description of WorkTimeGenerator.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Tests\Generators;


use Dots\WorkTimeSchedule\WorkTimeSchedule;

class WorkTimeGenerator
{
    public static function generateAlwaysOnWithInactiveDays(array $data = []): WorkTimeSchedule
    {
        $workTime = self::generateInactiveBeforeDayWorkTimeArray(7);
        return WorkTimeSchedule::fromArray(array_merge([
            'schedule' => $workTime,
            'timezone' => 'Europe/Kiev',
            'alwaysOn' => true
        ], $data));
    }

    public static function generate(array $data = []): WorkTimeSchedule
    {
        return WorkTimeSchedule::fromArray(array_merge([
            'schedule' => self::generateWorkTimeArray(),
            'timezone' => 'Europe/Kiev'
        ], $data));
    }

    public static function generateWithCustomDayData(int $numOfData, array $data): WorkTimeSchedule
    {
        $workTime = self::generateWorkTimeArray();
        $workTime[$numOfData] = $data;
        return WorkTimeSchedule::fromArray(array_merge([
            'schedule' => $workTime,
        ], $data));
    }

    public static function generateWithInactiveDay(int $numberOfDay, array $dayData = []): WorkTimeSchedule
    {
        return WorkTimeSchedule::fromArray(array_merge([
            'schedule' => self::getWorkTimeWithInactiveDay($numberOfDay, $dayData),
        ]));
    }

    public static function getWorkTimeWithInactiveDay(int $numberOfDay, array $data = []): array
    {
        $workTime = self::generateWorkTimeArray();
        $workTime[$numberOfDay] = array_merge([
            "id" => $numberOfDay,
            "status" => 0,
            "start" => "08:00",
            "end" => "08:00"
        ], $data);
        return $workTime;
    }

    public static function generateInactiveBeforeDayWorkTimeArray(int $day): array
    {
        $workTime = [];
        $status = 0;
        for ($i = 0; $i < 7; $i++) {
            if ($i == $day) {
                $status = 1;
            }
            $workTime[$i] = [
                "id" => $i,
                "status" => $status,
                "start" => "08:00",
                "end" => "23:00"
            ];
        }
        return $workTime;
    }

    public static function generateWorkTimeArray(): array
    {
        $workTime = [];
        for ($i = 0; $i < 7; $i++) {
            $workTime[$i] = [
                "id" => $i,
                "status" => 1,
                "start" => "08:00",
                "end" => "23:00"
            ];
        }
        return $workTime;
    }

    public static function generateWorkWithOneDay(): array
    {
        return [
            [
                "id" => 0,
                "status" => 1,
                "start" => "08:00",
                "end" => "23:00"
            ]
        ];
    }
}