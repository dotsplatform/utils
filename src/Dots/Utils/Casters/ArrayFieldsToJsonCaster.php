<?php
/**
 * Description of ArrayFieldsToJsonCaster.php
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Oleksandr Polosmak <o.polosmak@dotsplatform.com>
 */

namespace Dots\Utils\Casters;


use Illuminate\Contracts\Support\Arrayable;
use RuntimeException;

class ArrayFieldsToJsonCaster
{
    public function castItemsArrayFieldsToJson(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            if (is_array($item)) {
                $result[] = $this->castArrayFieldsToJson($item);

                continue;
            }

            if ($item instanceof Arrayable) {
                $result[] = $this->castArrayFieldsToJson(
                    $item->toArray(),
                );

                continue;
            }

            throw new RuntimeException('Array item cannot be converted to array');
        }

        return $result;
    }

    public function castArrayFieldsToJson(array $item): array
    {
        foreach ($item as $key => $value) {
            if (is_array($value)) {
                $item[$key] = json_encode($value);
            }
        }

        return $item;
    }
}