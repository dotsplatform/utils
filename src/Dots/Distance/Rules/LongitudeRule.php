<?php

/**
 * Description of LongitudeRule.php.
 *
 * Validation rule for a longitude field of a FormRequest: numeric (number or numeric string), finite and inside
 * [-180, 180], edges included. Combine it with "required" / "nullable" as usual, for example
 * ['required', new LongitudeRule()].
 *
 * The rule only checks the value; it never changes it.
 * To accept a longitude of a map panned past the antimeridian, wrap it with Position::normalizeLongitude()
 * (for example in prepareForValidation()) before this rule runs.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dots\Distance\Rules;

use Closure;
use Dots\Distance\Position;
use Illuminate\Contracts\Validation\ValidationRule;

class LongitudeRule implements ValidationRule
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

        if (! Position::isLongitudeInRange((float) $value)) {
            $fail($this->buildMessage());
        }
    }

    private function buildMessage(): string
    {
        return sprintf(
            'The :attribute field must be a longitude between %s and %s.',
            Position::MIN_LONGITUDE,
            Position::MAX_LONGITUDE,
        );
    }
}
