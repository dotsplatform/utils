<?php
/**
 * Description of PriceWithCurrencyFormatter.php.
 *
 * @copyright Copyright (c) MISTER.AM, LLC
 * @author    Igor Abdrazakov <igor@mister.am>
 */

namespace Dots\Utils;

use Dots\Data\Currency;

class PriceWithCurrencyFormatter
{
    public static function formatFromCents(int $cents, string $currency, string $lang): string
    {
        $price = round($cents / 100, 2);

        return self::format($price, $currency, $lang);
    }

    /**
     * @param  float|int|string  $price
     * @param  string  $currency
     * @param  string  $lang
     * @return string
     */
    public static function format($price, string $currency, string $lang): string
    {
        if (in_array($currency, [
            Currency::CURRENCY_USD, Currency::CURRENCY_EUR,
        ])) {
            return trans("currencies.$currency", [], $lang).$price;
        }

        return "$price ".trans("currencies.$currency", [], $lang);
    }
}
