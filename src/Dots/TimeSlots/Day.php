<?php
/**
 * Description of SlotDay.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Dots\TimeSlots;

use Dots\Data\DTO;

class Day extends DTO
{
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    protected int $id;
    protected int $status;
    protected Slots $slots;

    public static function fromArray(array $data): static
    {
        $data['slots'] = Slots::fromArray($data['slots'] ?? []);
        return parent::fromArray($data);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSlots(): Slots
    {
        return $this->slots;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->getStatus() === self::STATUS_ACTIVE;
    }
}
