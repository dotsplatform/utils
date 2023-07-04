<?php
/**
 * Description of SlotTime.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Dots\TimeSlots;

use Dots\Data\DTO;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class Slot extends DTO
{
    protected string $start;
    protected string $end;

    protected function assertConstructDataIsValid(array $data): void
    {
        $startResult = preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $data['start'] ?? null);
        $endResult = preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $data['end'] ?? null);
        if (!$startResult || !$endResult) {
            Log::error('Invalid time received', $data);
            throw new RuntimeException('Invalid time received');
        }

        parent::assertConstructDataIsValid($data);
    }

    public function getStart(): string
    {
        return $this->start;
    }

    public function getStartHours(): int
    {
        return (int)explode(':', $this->getStart())[0];
    }

    public function getStartMinutes(): int
    {
        return (int)explode(':', $this->getStart())[1];
    }

    public function getEndHours(): int
    {
        return (int)explode(':', $this->getEnd())[0];
    }

    public function getEndMinutes(): int
    {
        return (int)explode(':', $this->getEnd())[1];
    }

    public function getEnd(): string
    {
        return $this->end;
    }
}
