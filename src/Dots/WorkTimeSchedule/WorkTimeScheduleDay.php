<?php
/**
 * Description of WorkTimeScheduleDay.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Dots\WorkTimeSchedule;


use Dots\Data\DTO;

class WorkTimeScheduleDay extends DTO
{
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    protected int $id;
    protected Time $startTime;
    protected Time $endTime;
    protected int $status;

    public static function fromArray(array $data): static
    {
        $data['startTime'] = Time::fromArray($data['startTime'] ?? []);
        $data['endTime'] = Time::fromArray($data['endTime'] ?? []);
        return parent::fromArray($data);
    }

    public function isStatusActive(): bool
    {
        return $this->getStatus() === self::STATUS_ACTIVE;
    }

    public function isStatusInactive(): bool
    {
        return $this->getStatus() === self::STATUS_INACTIVE;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStartTime(): Time
    {
        return $this->startTime;
    }

    public function getEndTime(): Time
    {
        return $this->endTime;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}