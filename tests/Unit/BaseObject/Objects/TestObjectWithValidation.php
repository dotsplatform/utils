<?php
/**
 * Description of TestObjectWithValidation.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject\Objects;

use Dots\Data\DTO;
use InvalidArgumentException;

class TestObjectWithValidation extends DTO
{
    protected string $id = '';

    protected function assertConstructDataIsValid(array $data): void
    {
        if (empty($data['id'])) {
            throw new InvalidArgumentException('id is required');
        }
    }

    public function getId(): string
    {
        return $this->id;
    }
}
