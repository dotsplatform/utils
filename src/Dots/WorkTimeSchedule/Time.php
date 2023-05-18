<?php
/**
 * Description of Time.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Dots\WorkTimeSchedule;


use Dots\Data\DTO;
use Dots\WorkTimeSchedule\Exceptions\InvalidDayTimeException;

class Time extends DTO
{
    protected string $time;

    protected function assertConstructDataIsValid(array $data): void
    {
        $time = $data['time'] ?? null;
        $result = preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time);
        if (! $result) {
            throw new InvalidDayTimeException("Invalid time - {$time} received");
        }

        parent::assertConstructDataIsValid($data);
    }

    public function getTime(): string
    {
        return $this->time;
    }
}