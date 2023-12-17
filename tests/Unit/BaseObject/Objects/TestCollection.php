<?php
/**
 * Description of TestCollection.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject\Objects;


use Dots\Data\FromArrayable;
use Illuminate\Support\Collection;

class TestCollection extends Collection implements FromArrayable
{
    public static function fromArray(array $data): static
    {
       return new static($data);
    }
}