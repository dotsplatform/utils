<?php
/**
 * Description of Base64Url.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Dots\Utils;


class Base64Url
{
    public static function decodeToArray(string $data): array
    {
        $base64EncodedString = str_replace(['-', '_'], ['+', '/'], $data);

        // Pad with '=' characters if necessary
        $padding = strlen($base64EncodedString) % 4;
        if ($padding) {
            $base64EncodedString .= str_repeat('=', 4 - $padding);
        }

        // Decode the Base64 encoded string
        $decodedString = base64_decode($base64EncodedString);

        if (!$decodedString) {
            return [];
        }

        $decodedData = json_decode($decodedString, true);
        if (!is_array($decodedData)) {
            return [];
        }

        return $decodedData;
    }
}