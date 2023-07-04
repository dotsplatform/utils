<?php

use Carbon\Carbon;
use Dots\TimeSlots\TimeSlots;
use Tests\Generators\WorkTimeGenerator;
use Tests\TestCase;

/**
 * Description of WorkTimeScheduleTest.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */
class WorkTimeScheduleTest extends TestCase
{
    public function testFromArrayExpectsOk(): void
    {
        $data = WorkTimeGenerator::generateWorkTimeArray();
        $timezone = $this->getBaseTimeZone();

        $schedule = TimeSlots::fromArray([
            'days' => $data,
            'anytime' => true,
            'timezone' => $timezone,
        ]);

        $this->assertEquals($data, $schedule->getDays()->toArray());
        $this->assertTrue($schedule->isAnytime());
        $this->assertEquals($timezone, $schedule->getTimezone());
    }

    public function testFromArrayExpectsDefaultValuesFilled(): void
    {
        $data = WorkTimeGenerator::generateWorkTimeArray();
        $schedule = TimeSlots::fromArray([
            'days' => $data,
            'timezone' => $this->getBaseTimeZone(),
        ]);

        $this->assertEquals($data, $schedule->getDays()->toArray());
        $this->assertEquals($this->getBaseTimeZone(), $schedule->getTimezone());
        $this->assertFalse($schedule->isAnytime());
    }

    public function testIsWorkingNowExpectsYes(): void
    {
        $schedule = WorkTimeGenerator::generate();
        $this->assertTrue($schedule->isWorkingNow());
    }

    public function testIsWorkingNowExpectsNoIfInactiveDay(): void
    {
        $day = $this->getCarbonNow()->format('N') - 1;
        $schedule = WorkTimeGenerator::generateWithInactiveDay($day);
        $this->assertFalse($schedule->isWorkingNow());
    }

    public function testIsWorkingNowExpectsNoIfEmptySchedule(): void
    {
        $schedule = WorkTimeGenerator::generateWithEmptyDays();
        $this->assertFalse($schedule->isWorkingNow());
    }

    public function testIsWorkingNowExpectsNoInDayIsActiveButAlreadyDoNotWork(): void
    {
        $day = $this->getCarbonNow()->format('N') - 1;
        $workTimeData = [
            'id' => $day,
            'status' => 1,
            'slots' => [
                [
                    'start' => $this->getCarbonNow()->subHours(2)->format('H:i'),
                    'end' => $this->getCarbonNow()->subHour()->format('H:i'),
                ]
            ],
        ];
        $schedule = WorkTimeGenerator::generateWithInactiveDay($day, $workTimeData);
        $this->assertFalse($schedule->isWorkingNow());
    }

    public function testIsWorkingAtTimeExpectsYes(): void
    {
        $schedule = WorkTimeGenerator::generate();
        $this->assertTrue($schedule->isWorkingAtTime(time() + 3600));
    }

    public function testIsWorkingAtTImeExpectsNoIfInactiveDay(): void
    {
        $day = $this->getCarbonNow()->format('N') - 1;
        $schedule = WorkTimeGenerator::generateWithInactiveDay($day);
        $this->assertFalse($schedule->isWorkingAtTime(time() + 3600));
    }

    public function testIsWorkingAtTimeExpectsNoIfEmptySchedule(): void
    {
        $schedule = WorkTimeGenerator::generateWithEmptyDays();
        $this->assertFalse($schedule->isWorkingAtTime(time() + 3600));
    }

    public function testIsWorkingAtTimeExpectsNoInDayIsActiveButAlreadyDoNotWork(): void
    {
        $day = $this->getCarbonNow()->format('N') - 1;
        $workTimeData[$day] = [
            'id' => $day,
            'status' => 1,
            'slots' => [
                [
                    'start' => $this->getCarbonNow()->subHours(3)->format('h:i'),
                    'end' => $this->getCarbonNow()->subHour()->format('h:i'),
                ]
            ],
        ];
        $schedule = WorkTimeGenerator::generateWithInactiveDay($day, $workTimeData);
        $this->assertFalse($schedule->isWorkingAtTime($this->getCarbonNow()->subHours(2)->timestamp));
    }

    public function testIsWorkingDayExpectsYes(): void
    {
        $schedule = WorkTimeGenerator::generate();
        $this->assertTrue($schedule->isWorkingDay(time()));
    }

    public function testIsWorkingDayExpectsNoIfInactiveDay(): void
    {
        $day = $this->getCarbonNow()->format('N') - 1;
        $schedule = WorkTimeGenerator::generateWithInactiveDay($day);
        $this->assertFalse($schedule->isWorkingDay(time()));
    }

    public function testIsWorkingDayExpectsNoIfEmptySchedule(): void
    {
        $schedule = WorkTimeGenerator::generateWithEmptyDays();
        $this->assertFalse($schedule->isWorkingDay(time()));
    }

    public function testIsWorkingDayExpectsNoInDayIsActiveButAlreadyDoNotWork(): void
    {
        $day = $this->getCarbonNow()->format('N') - 1;
        $workTimeData[$day] = [
            'id' => $day,
            'status' => 1,
            'slots' => [
                [
                    'start' => $this->getCarbonNow()->subHours(2)->format('h:i'),
                    'end' => $this->getCarbonNow()->subHour()->format('h:i'),
                ]
            ],
        ];
        $schedule = WorkTimeGenerator::generateWithInactiveDay($day, $workTimeData);
        $this->assertFalse($schedule->isWorkingDay(time()));
    }

    public function testGetWorkTimeTodayExpectsNull(): void
    {
        $schedule = WorkTimeGenerator::generateWithEmptyDays();
        $this->assertNull($schedule->getWorkTimeToday());
    }

    public function testGetWorkTimeTodayExpectsData(): void
    {
        $day = $this->getCarbonNow()->format('N') - 1;
        $schedule = WorkTimeGenerator::generate();
        $this->assertEquals(
            $schedule->getDays()->findDay($day),
            $schedule->getWorkTimeToday(),
        );
    }

    public function testGetStartTimeTodayExpectsNullIfEmpty(): void
    {
        $schedule = WorkTimeGenerator::generateWithEmptyDays();
        $this->assertNull($schedule->getStartTimeToday());
    }

    /**
     * @group testGetStartTimeTodayExpectsTime
     */
    public function testGetStartTimeTodayExpectsTime(): void
    {
        $day = $this->getCarbonNow()->format('N') - 1;
        $start = $this->getCarbonNow()->subHours(3);
        $schedule = WorkTimeGenerator::generateWithCustomDayData($day, [
            'id' => $day,
            'status' => 1,
            'slots' => [
                [
                    'start' => $start->format('H:i'),
                    'end' => $this->getCarbonNow()->subHour()->format('H:i'),
                ]
            ]
        ]);
        $this->assertTimestampsAreEqualsInAccuracyToMinute($start->timestamp, $schedule->getStartTimeToday());
    }

    public function testGetEndTimeTodayExpectsNullIfEmpty(): void
    {
        $schedule = WorkTimeGenerator::generateWithEmptyDays();
        $this->assertNull($schedule->getEndTimeToday());
    }

    public function testGetEndTimeTodayExpectsTime(): void
    {
        $day = $this->getCarbonNow()->format('N') - 1;
        $end = $this->getCarbonNow()->subHour();
        $schedule = WorkTimeGenerator::generateWithCustomDayData($day, [
            'id' => $day,
            'status' => 1,
            'slots' => [
                [
                    'start' => $this->getCarbonNow()->subHours(3)->format('H:i'),
                    'end' => $end->format('H:i'),
                ]
            ],
        ]);
        $this->assertTimestampsAreEqualsInAccuracyToMinute($end->timestamp, $schedule->getEndTimeToday());
    }

    /** @group testGetNearestStartTimeExpectsNullIfEmpty */
    public function testGetNearestStartTimeExpectsNullIfEmpty(): void
    {
        $schedule = WorkTimeGenerator::generateWithEmptyDays();
        $this->assertNull($schedule->getNearestStartTime(time() + 3600));
    }

    /** @group testGetNearestStartTimeExpectsTime */
    public function testGetNearestStartTimeExpectsTime(): void
    {
        $currentDay = $this->getCarbonNow()->format('N') - 1;
        $expectedStartTime = $this->getCarbonNow()->addDay();
        $workTime = WorkTimeGenerator::generateInactiveBeforeDayWorkTimeArray($currentDay + 1);
        $workTime[$currentDay + 1]['slots'][0]['start'] = $expectedStartTime->format('H:i');
        $schedule = TimeSlots::fromArray([
            'days' => $workTime,
            'timezone' => $this->getBaseTimeZone(),
        ]);
        $this->assertTimestampsAreEqualsInAccuracyToMinute(
            $expectedStartTime->timestamp,
            $schedule->getNearestStartTimeForNow(),
        );
    }

    /** @group testGetNearestStartTimeExpectsCurrentDay */
    public function testGetNearestStartTimeExpectsCurrentDay(): void
    {
        $currentDay = $this->getCarbonNow()->format('N') - 1;
        $expectedStartTime = $this->getCarbonNow();
        $workTime = WorkTimeGenerator::generateInactiveBeforeDayWorkTimeArray($currentDay);
        $workTime[$currentDay]['slots'][0]['start'] = $expectedStartTime->format('H:i');
        $schedule = TimeSlots::fromArray([
            'days' => $workTime,
            'timezone' => $this->getBaseTimeZone(),
        ]);
        $this->assertTimestampsAreEqualsInAccuracyToMinute(
            $expectedStartTime->timestamp,
            $schedule->getNearestStartTimeForNow(),
        );
    }

    public function testIsWorkingNowExpectsYesIfAlwaysOn(): void
    {
        $schedule = WorkTimeGenerator::generateAlwaysOnWithInactiveDays();
        $this->assertTrue($schedule->isWorkingNow());
    }

    public function testIsWorkingAtTimeExpectsYesIfAlwaysOn(): void
    {
        $schedule = WorkTimeGenerator::generateAlwaysOnWithInactiveDays();
        $this->assertTrue($schedule->isWorkingAtTime(time() + 3600));
    }

    public function testIsWorkingDayExpectsYesIfAlwaysOn(): void
    {
        $schedule = WorkTimeGenerator::generateAlwaysOnWithInactiveDays();
        $this->assertTrue($schedule->isWorkingDay(time() + 3600));
    }

    private function getCarbonNow(): Carbon
    {
        return Carbon::now($this->getBaseTimeZone());
    }

    private function getBaseTimeZone(): string
    {
        return 'Europe/Kiev';
    }
}