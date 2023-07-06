<?php

use Carbon\Carbon;
use Dots\WorkTimeSchedule\Day;
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
        $timezone = $this->getBaseTimeZone();

        $schedule = WorkTimeSchedule::fromArray([
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
        $schedule = WorkTimeSchedule::fromArray([
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

    public function testGetNearestStartTimeExpectsNullIfEmpty(): void
    {
        $schedule = WorkTimeGenerator::generateWithEmptyDays();
        $this->assertNull($schedule->getNearestStartTime(time() + 3600));
    }

    public function testGetNearestStartTimeExpectsTime(): void
    {
        $currentDay = $this->getCarbonNow()->format('N') - 1;
        $expectedStartTime = $this->getCarbonNow()->addDay();
        $workTime = WorkTimeGenerator::generateInactiveBeforeDayWorkTimeArray($currentDay + 1);
        $workTime[$currentDay + 1]['slots'][0]['start'] = $expectedStartTime->format('H:i');
        $schedule = WorkTimeSchedule::fromArray([
            'days' => $workTime,
            'timezone' => $this->getBaseTimeZone(),
        ]);
        $this->assertTimestampsAreEqualsInAccuracyToMinute(
            $expectedStartTime->timestamp,
            $schedule->getNearestStartTimeForNow(),
        );
    }

    public function testGetNearestStartTimeExpectsSecondSlotToday(): void
    {
        $currentDay = $this->getCarbonNow()->format('N') - 1;
        $time = $this->getCarbonNow()->setTimeFromTimeString('15:00');
        $expectedStartTime = (clone $time)->addHours(2)->getTimestamp();
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
        $schedule = WorkTimeGenerator::generateWithCustomDayData($currentDay, $dayData);
        $this->assertTimestampsAreEqualsInAccuracyToMinute(
            $expectedStartTime,
            $schedule->getNearestStartTime($time->getTimestamp()),
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

    public function testGenerateTranslatedSlotsByDaysExpectsFirstDaySlotIsNearestButNotFirst(): void
    {
        $time = $this->getCarbonNow();
        $schedule = WorkTimeGenerator::generateScheduleForDayWithTwoSlotsNotActiveForTime($time->getTimestamp());

        $slots = $schedule->generateTranslatedSlotsByDays($time->getTimestamp());

        $expectedFirstStartTime = $schedule->getDays()->findActiveDay(abs($time->dayOfWeekIso - 1))
            ->getSlots()
            ->findNearestSlot($time->getTimestamp(), $this->getBaseTimeZone())
            ->getStart();
        $this->assertTimestampsAreEqualsInAccuracyToMinute(
            (clone $time)->startOfDay()->getTimestamp(),
            $slots[0]['date'],
        );
        $this->assertTimestampsAreEqualsInAccuracyToMinute(
            (clone $time)->setTimeFromTimeString($expectedFirstStartTime)->getTimestamp(),
            $slots[0]['times'][0]['start'],
        );
    }

    public function testGenerateTranslatedSlotsByDaysExpectsEmptyIfDaysInaction(): void
    {
        $schedule = WorkTimeGenerator::generateAlwaysOnWithInactiveDays();

        $slots = $schedule->generateTranslatedSlotsByDays(time());
        $this->assertEmpty($slots);
    }

    public function testGenerateTranslatedSlotsByDaysExpectsFirstSlotIsFirstSlotOfDay(): void
    {
        $time = $this->getCarbonNow()->addDay()->setTimeFromTimeString('04:00');
        $schedule = WorkTimeGenerator::generate();

        $slots = $schedule->generateTranslatedSlotsByDays($time->getTimestamp());
        $this->assertTimestampsAreEqualsInAccuracyToMinute(
            (clone $time)->startOfDay()->getTimestamp(),
            $slots[0]['date'],
        );
        $expectedFirstStartTime = $schedule->getDays()->findDay(abs($time->dayOfWeekIso - 1))
            ->getSlots()
            ->first()
            ->getStart();
        $this->assertTimestampsAreEqualsInAccuracyToMinute(
            (clone $time)->setTimeFromTimeString($expectedFirstStartTime)->getTimestamp(),
            $slots[0]['times'][0]['start'],
        );
    }

    public function testFindNearestSlotIfTimeIsGreatThatStartOfSlotExpectsNextSlot(): void
    {
        $time = $this->getCarbonNow()->setTimeFromTimeString('10:30');
        $expectedStartTime = '12:00';
        $schedule = WorkTimeGenerator::generateWithCustomSlots();

        $nearestStartTime = $schedule->getNearestStartTime($time->getTimestamp());
        $this->assertEquals(
            $expectedStartTime,
            Carbon::createFromTimestamp($nearestStartTime, $this->getBaseTimeZone())->format('H:i'),
        );
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
