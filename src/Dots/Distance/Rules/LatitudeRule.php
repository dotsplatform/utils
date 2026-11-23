<?php

/**
 * Description of LatitudeRule.php.
 *
 * Validation rule for a latitude field of a FormRequest: numeric (number or numeric string), finite and inside
 * [-90, 90], edges included. Combine it with "required" / "nullable" as usual, for example
 * ['required', new LatitudeRule()].
 *
 * The rule only checks the value; it never changes it.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dots\Distance\Rules;

use Closure;
use Dots\Distance\Position;
use Illuminate\Contracts\Validation\ValidationRule;

class LatitudeRule implements ValidationRule
{
    /**
     * @param Closure(string, ?string=): mixed $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value)) {
            $fail($this->buildMessage());

            return;
        }

        if (! Position::isLatitudeInRange((float) $value)) {
            $fail($this->buildMessage());
        }
    }

    private function buildMessage(): string
    {
        return sprintf(
            'The :attribute field must be a latitude between %s and %s.',
            Position::MIN_LATITUDE,
            Position::MAX_LATITUDE,
        );
    }
}
