<?php
/**
 * Description of VersionChecker.php.
 *
 * @copyright Copyright (c) MISTER.AM, LLC
 * @author    Egor Gerasimchuk <egor@mister.am>
 */

namespace Dots\Versions;

class VersionChecker
{
    public function isVersionGreater(string $versionA, string $versionB): bool
    {
        return $this->versionCmp($versionA, $versionB) > 0;
    }

    public function isVersionGreaterOrEqual(string $versionA, string $versionB): bool
    {
        return $this->versionCmp($versionA, $versionB) >= 0;
    }

    /**
     * Returns:
     * 1 - if A greater B
     * -1 if B greater A
     * 0 if equals.
     *
     * @param  string  $versionA
     * @param  string  $versionB
     * @return int
     */
    private function versionCmp(string $versionA, string $versionB): int
    {
        $versionBParts = explode('.', $versionB);
        $versionAParts = explode('.', $versionA);
        foreach ($versionBParts as $index => $versionBPart) {
            if (! $versionBPart) {
                continue;
            }
            if (! isset($versionAParts[$index])) {
                return -1;
            }
            $versionAPart = $versionAParts[$index];

            if ($versionAPart === $versionBPart) {
                continue;
            }

            return $versionAPart > $versionBPart ? 1 : -1;
        }

        return 0;
    }
}
