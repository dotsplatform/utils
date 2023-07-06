<?php
/**
 * Description of WorkTimeGenerator.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Tests\Generators;

use Carbon\Carbon;
use Dots\WorkTimeSchedule\Day;
use Dots\WorkTimeSchedule\WorkTimeSchedule;

class WorkTimeGenerator
{
    public const BASE_SLOT_START = '08:00';
    public const BASE_SLOT_END = '23:00';
    public const BASE_SLOT_INTERVAL = 2;
    public const BASE_DAY_STATUS = Day::STATUS_ACTIVE;

    public static function generateAlwaysOnWithInactiveDays(array $data = []): WorkTimeSchedule
    {
        $workTime = self::generateInactiveBeforeDayWorkTimeArray(7);
        return WorkTimeSchedule::fromArray(array_merge([
            'days' => $workTime,
            'timezone' => self::getBaseTimeZone(),
            'anytime' => true,
        ], $data));
    }

    public static function generate(array $data = []): WorkTimeSchedule
    {
        return WorkTimeSchedule::fromArray(array_merge([
            'days' => self::generateWorkTimeArray(),
            'timezone' => self::getBaseTimeZone(),
        ], $data));
    }

    public static function generateWithEmptyDays(array $data = []): WorkTimeSchedule
    {
        return WorkTimeSchedule::fromArray(array_merge([
            'days' => [],
            'timezone' => self::getBaseTimeZone(),
        ], $data));
    }

    public static function generateWithCustomDayData(int $numOfData, array $data): WorkTimeSchedule
    {
        $workTime = self::generateWorkTimeArray();
        $workTime[$numOfData] = $data;
        return WorkTimeSchedule::fromArray(array_merge([
            'days' => $workTime,
            'timezone' => self::getBaseTimeZone(),
        ], $data));
    }

    public static function generateWithInactiveDay(int $numberOfDay, array $dayData = []): WorkTimeSchedule
    {
        return WorkTimeSchedule::fromArray(array_merge([
            'days' => self::getWorkTimeWithInactiveDay($numberOfDay, $dayData),
            'timezone' => self::getBaseTimeZone(),
        ]));
    }

    public static function getWorkTimeWithInactiveDay(int $numberOfDay, array $data = []): array
    {
        $workTime = self::generateWorkTimeArray();
        $workTime[$numberOfDay] = array_merge([
            'id' => $numberOfDay,
            'status' => 0,
            'slots' => [
                [
                    'start' => '08:00',
                    'end' => '08:00',
                ]
            ],
        ], $data);
        return $workTime;
    }

    public static function generateScheduleForDayWithTwoSlotsNotActiveForTime(int $timestamp): WorkTimeSchedule
    {
        $time = Carbon::createFromTimestamp($timestamp, self::getBaseTimeZone());
        $currentDay = abs($time->dayOfWeekIso - 1);
        $dayData = [
            'id' => $currentDay,
            'status' => Day::STATUS_ACTIVE,
            'slots' => [
                [
                    'start' => (clone $time)->subHours(3)->format('H:i'),
                    'end' => (clone $time)->subHours(2)->format('H:i'),
                ],
                [
                    'start' => (clone $time)->addHours(2)->format('H:i'),
                    'end' => (clone $time)->addHours(3)->format('H:i'),
                ],
            ],
        ];

        return self::generateWithCustomDayData($currentDay, $dayData);
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
                'id' => $i,
                'status' => $status,
                'slots' => [
                    [
                        'start' => '08:00',
                        'end' => '23:00',
                    ]
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
                'id' => $i,
                'status' => 1,
                'slots' => [
                    [
                        'start' => '08:00',
                        'end' => '23:00',
                    ]
                ],
            ];
        }
        return $workTime;
    }

    public static function generateWithCustomSlots(array $data = []): WorkTimeSchedule
    {
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = self::generateDay(
                $i,
                $data[$i]['start'] ?? self::BASE_SLOT_START,
                $data[$i]['end'] ?? self::BASE_SLOT_END,
                $data[$i]['hoursInterval'] ?? self::BASE_SLOT_INTERVAL,
                $data[$i]['status'] ?? self::BASE_DAY_STATUS,
            )->toArray();
        }

        return WorkTimeSchedule::fromArray([
            'days' => $days,
            'timezone' => self::getBaseTimezone(),
        ]);
    }

    public static function generateDay(
        int $id,
        string $start = self::BASE_SLOT_START,
        string $end = self::BASE_SLOT_END,
        float $hoursInterval = self::BASE_SLOT_INTERVAL,
        int $status = self::BASE_DAY_STATUS,
    ): Day {
        $startTime = Carbon::createFromFormat('H:i', $start, self::getBaseTimeZone());
        $endTime = Carbon::createFromFormat('H:i', $end, self::getBaseTimeZone());
        $countOfSlots = (int)(($endTime->hour - $startTime->hour) / $hoursInterval);
        $slots = [];

        for ($i = 0; $i < $countOfSlots; $i++) {
            $slots[] = [
                'start' => $startTime->format('H:i'),
                'end' => (clone $startTime)->addMinutes($hoursInterval * 60)->format('H:i'),
            ];

            $startTime = $startTime->addMinutes($hoursInterval * 60);
        }
        return Day::fromArray([
            'id' => $id,
            'status' => $status,
            'slots' => $slots,
        ]);
    }

    private static function getBaseTimeZone(): string
    {
        return 'Europe/Kiev';
    }
}
