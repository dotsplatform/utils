<?php
/**
 * Description of UuidGenerator.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Dots\Utils;

use Illuminate\Support\Str;

class UuidGenerator
{
    public function generate(): string
    {
        return (string) Str::uuid();
    }
}
