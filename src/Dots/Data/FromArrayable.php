<?php
/**
 * Description of FromArrayble.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Dots\Data;

interface FromArrayable
{
    public static function fromArray(array $data): static;
}