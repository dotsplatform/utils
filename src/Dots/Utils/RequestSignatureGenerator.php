<?php
/**
 * Description of RequestSignatureGenerator.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Dots\Utils;

class RequestSignatureGenerator
{
    public function generate(string $key, array $data): string
    {
        $encodedData = $this->encodeData($data);

        return sha1("$key|".$encodedData);
    }

    private function encodeData(array $data): string
    {
        $data = $this->sortData($data);

        return base64_encode(json_encode($data) ?: '');
    }

    private function sortData(array $data): array
    {
        ksort($data);

        return $data;
    }
}
