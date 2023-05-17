<?php

use Carbon\Carbon;
use Dots\WorkTimeSchedule\WorkTimeSchedule;
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
        $timezone = 'Europe\Kiev';

        $schedule = WorkTimeSchedule::fromArray([
            'schedule' => $data,
            'alwaysOn' => true,
            'timezone' => $timezone,
        ]);

        $this->assertEquals($data, $schedule->getSchedule());
        $this->assertTrue($schedule->isAlwaysOn());
        $this->assertEquals($timezone, $schedule->getTimezone());
    }

    public function testFromArrayExpectsDefaultValuesFilled(): void
    {
        $data = WorkTimeGenerator::generateWorkTimeArray();
        $schedule = WorkTimeSchedule::fromArray([
            'schedule' => $data,
        ]);

        $this->assertEquals($data, $schedule->getSchedule());
        $this->assertFalse($schedule->isAlwaysOn());
        $this->assertNull($schedule->getTimezone());
    }

    public function testIsWorkingNowExpectsYes(): void
    {
        $schedule = WorkTimeGenerator::generate();
        $this->assertTrue($schedule->isWorkingNow());
    }

    public function testIsWorkingNowExpectsNoIfInactiveDay(): void
    {
        $day = Carbon::now()->format('N') - 1;
        $schedule = WorkTimeGenerator::generateWithInactiveDay($day);
        $this->assertFalse($schedule->isWorkingNow());
    }

    public function testIsWorkingNowExpectsNoIfEmptySchedule(): void
    {
        $schedule = WorkTimeSchedule::fromArray([]);
        $this->assertFalse($schedule->isWorkingNow());
    }

    public function testIsWorkingNowExpectsNoInDayIsActiveButAlreadyDoNotWork(): void
    {
        $day = Carbon::now()->format('N') - 1;
        $workTimeData = [
            "id" => $day,
            "status" => 1,
            "start" => Carbon::now()->subHours(2)->format('H:i'),
            "end" => Carbon::now()->subHour()->format('H:i'),
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
        $day = Carbon::now()->format('N') - 1;
        $schedule = WorkTimeGenerator::generateWithInactiveDay($day);
        $this->assertFalse($schedule->isWorkingAtTime(time() + 3600));
    }

    public function testIsWorkingAtTimeExpectsNoIfEmptySchedule(): void
    {
        $schedule = WorkTimeSchedule::fromArray([]);
        $this->assertFalse($schedule->isWorkingAtTime(time() + 3600));
    }

    public function testIsWorkingAtTimeExpectsNoInDayIsActiveButAlreadyDoNotWork(): void
    {
        $day = Carbon::now()->format('N') - 1;
        $workTimeData[$day] = [
            "id" => $day,
            "status" => 1,
            "start" => Carbon::now()->subHours(3)->format('h:i'),
            "end" => Carbon::now()->subHour()->format('h:i'),
        ];
        $schedule = WorkTimeGenerator::generateWithInactiveDay($day, $workTimeData);
        $this->assertFalse($schedule->isWorkingAtTime(Carbon::now()->subHours(2)->timestamp));
    }

    public function testIsWorkingDayExpectsYes(): void
    {
        $schedule = WorkTimeGenerator::generate();
        $this->assertTrue($schedule->isWorkingDay(time()));
    }

    public function testIsWorkingDayExpectsNoIfInactiveDay(): void
    {
        $day = Carbon::now()->format('N') - 1;
        $schedule = WorkTimeGenerator::generateWithInactiveDay($day);
        $this->assertFalse($schedule->isWorkingDay(time()));
    }

    public function testIsWorkingDayExpectsNoIfEmptySchedule(): void
    {
        $schedule = WorkTimeSchedule::fromArray([]);
        $this->assertFalse($schedule->isWorkingDay(time()));
    }

    public function testIsWorkingDayExpectsNoInDayIsActiveButAlreadyDoNotWork(): void
    {
        $day = Carbon::now()->format('N') - 1;
        $workTimeData[$day] = [
            "id" => $day,
            "status" => 1,
            "start" => Carbon::now()->subHours(2)->format('h:i'),
            "end" => Carbon::now()->subHour()->format('h:i'),
        ];
        $schedule = WorkTimeGenerator::generateWithInactiveDay($day, $workTimeData);
        $this->assertFalse($schedule->isWorkingDay(time()));
    }

    public function testIsEmptyWorkTimeExpectsNo(): void
    {
        $schedule = WorkTimeGenerator::generate();
        $this->assertFalse($schedule->isEmptyWorkTime());
    }

    public function testIsEmptyWorkTimeExpectsYes(): void
    {
        $schedule = WorkTimeSchedule::empty();
        $this->assertTrue($schedule->isEmptyWorkTime());
    }

    public function testGetWorkTimeTodayExpectsNull(): void
    {
        $schedule = WorkTimeSchedule::empty();
        $this->assertNull($schedule->getWorkTimeToday());
    }

    public function testGetWorkTimeTodayExpectsData(): void
    {
        $day = Carbon::now()->format('N') - 1;
        $schedule = WorkTimeGenerator::generate();
        $this->assertEquals(
            $schedule->getSchedule()[$day],
            $schedule->getWorkTimeToday(),
        );
    }

    // need discuss, It should be null
    public function testGetStartTimeTodayExpectsNullIfEmpty(): void
    {
        $schedule = WorkTimeSchedule::empty();
        $this->assertFalse($schedule->getStartTimeToday());
    }

    public function testGetStartTimeTodayExpectsTime(): void
    {
        $day = Carbon::now()->format('N') - 1;
        $start = Carbon::now()->subHours(3);
        $schedule = WorkTimeGenerator::generateWithCustomDayData($day, [
            "id" => $day,
            "status" => 1,
            "start" => $start->format('H:i'),
            "end" => Carbon::now()->subHour()->format('H:i'),
        ]);
        $this->assertTimestampsAreEqualsInAccuracyToMinute($start->timestamp, $schedule->getStartTimeToday());
    }

    // need discuss, It should be null
    public function testGetEndTimeTodayExpectsNullIfEmpty(): void
    {
        $schedule = WorkTimeSchedule::empty();
        $this->assertFalse($schedule->getEndTimeToday());
    }

    public function testGetEndTimeTodayExpectsTime(): void
    {
        $day = Carbon::now()->format('N') - 1;
        $end = Carbon::now()->subHour();
        $schedule = WorkTimeGenerator::generateWithCustomDayData($day, [
            "id" => $day,
            "status" => 1,
            "start" => Carbon::now()->subHours(3)->format('H:i'),
            "end" => $end->format('H:i'),
        ]);
        $this->assertTimestampsAreEqualsInAccuracyToMinute($end->timestamp, $schedule->getEndTimeToday());
    }

    public function testGetNearestStartTimeExpectsNullIfEmpty(): void
    {
        $schedule = WorkTimeSchedule::empty();
        $this->assertNull($schedule->getNearestStartTime(time() + 3600));
    }

    public function testGetNearestStartTimeExpectsTime(): void
    {
        $currentDay = Carbon::now()->format('N') - 1;
        $expectedStartTime = Carbon::now()->addDay();
        $workTime = WorkTimeGenerator::generateInactiveBeforeDayWorkTimeArray($currentDay + 1);
        $workTime[$currentDay + 1]['start'] = $expectedStartTime->format('H:i');
        $schedule = WorkTimeSchedule::fromArray([
            'schedule' => $workTime
        ]);
        $this->assertTimestampsAreEqualsInAccuracyToMinute(
            $expectedStartTime->timestamp,
            $schedule->getNearestStartTime(time()),
        );
    }

    public function testGetNearestStartTimeExpectsCurrentDay(): void
    {
        $currentDay = Carbon::now()->format('N') - 1;
        $expectedStartTime = Carbon::now();
        $workTime = WorkTimeGenerator::generateInactiveBeforeDayWorkTimeArray($currentDay);
        $workTime[$currentDay]['start'] = $expectedStartTime->format('H:i');
        $schedule = WorkTimeSchedule::fromArray([
            'schedule' => $workTime
        ]);
        $this->assertTimestampsAreEqualsInAccuracyToMinute(
            $expectedStartTime->timestamp,
            $schedule->getNearestStartTime(time()),
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
}