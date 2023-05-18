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
            'workTimeScheduleDays' => $workTime,
            'timezone' => self::getBaseTimeZone(),
            'anytime' => true,
        ], $data));
    }

    public static function generate(array $data = []): WorkTimeSchedule
    {
        return WorkTimeSchedule::fromArray(array_merge([
            'workTimeScheduleDays' => self::generateWorkTimeArray(),
            'timezone' => self::getBaseTimeZone(),
        ], $data));
    }

    public static function generateWithEmptyDays(array $data = []): WorkTimeSchedule
    {
        return WorkTimeSchedule::fromArray(array_merge([
            'workTimeScheduleDays' => [],
            'timezone' => self::getBaseTimeZone(),
        ], $data));
    }

    public static function generateWithCustomDayData(int $numOfData, array $data): WorkTimeSchedule
    {
        $workTime = self::generateWorkTimeArray();
        $workTime[$numOfData] = $data;
        return WorkTimeSchedule::fromArray(array_merge([
            'workTimeScheduleDays' => $workTime,
            'timezone' => self::getBaseTimeZone(),
        ], $data));
    }

    public static function generateWithInactiveDay(int $numberOfDay, array $dayData = []): WorkTimeSchedule
    {
        return WorkTimeSchedule::fromArray(array_merge([
            'workTimeScheduleDays' => self::getWorkTimeWithInactiveDay($numberOfDay, $dayData),
            'timezone' => self::getBaseTimeZone(),
        ]));
    }

    public static function getWorkTimeWithInactiveDay(int $numberOfDay, array $data = []): array
    {
        $workTime = self::generateWorkTimeArray();
        $workTime[$numberOfDay] = array_merge([
            "id" => $numberOfDay,
            "status" => 0,
            "startTime" => [
                'time' => '08:00',
            ],
            "endTime" => [
                'time' => '08:00',
            ],
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
                "startTime" => [
                    'time' => '08:00',
                ],
                "endTime" => [
                    'time' => '23:00',
                ],
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
                "startTime" => [
                    'time' => '08:00',
                ],
                "endTime" => [
                    'time' => '23:00',
                ],
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
                "startTime" => [
                    'time' => '08:00',
                ],
                "endTime" => [
                    'time' => '23:00',
                ],
            ]
        ];
    }

    private static function getBaseTimeZone(): string
    {
        return 'Europe/Kiev';
    }
}