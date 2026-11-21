<?php
/**
 * Description of TestObjectWithStaticProperty.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Tests\Unit\BaseObject\Objects;

use Dots\Data\DTO;

class TestObjectWithStaticProperty extends DTO
{
    public static string $staticValue = 'initial';

    protected string $id = '';
}
