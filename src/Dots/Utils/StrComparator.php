<?php
/**
 * Description of StrCompare.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Dots\Utils;

class StrComparator
{
    private array $replacingCharacters = [
        'і' => 'и',
        'ї' => 'и',
        'є' => 'е',
    ];

    public function compare(string $a, string $b): int
    {
        $a = mb_strtolower($a);
        $b = mb_strtolower($b);

        $result = strcmp(
            $this->replaceChars($a),
            $this->replaceChars($b),
        );
        if ($result === 0) {
            return 0;
        }

        return $result > 0 ? 1 : -1;
    }

    private function replaceChars(string $subject): string
    {
        return str_replace(
            array_keys($this->replacingCharacters),
            array_values($this->replacingCharacters),
            $subject,
        );
    }
}
