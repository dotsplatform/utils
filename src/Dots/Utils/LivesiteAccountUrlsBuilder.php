<?php
/**
 * Description of LivesiteAccountUrlBuilder.php.
 *
 * @copyright Copyright (c) MISTER.AM, LLC
 * @author    Igor Abdrazakov <igor@mister.am>
 */

namespace Dots\Utils;

class LivesiteAccountUrlsBuilder
{
    public const MONITORING_PANEL_URL = '/p/monitoring/all';
    public const USER_ORDERS_HISTORY_URL_TEMPLATE = '/profile/orders-history?phone=%s';
    public const USER_STATS_URL_TEMPLATE = '/%s?stat-phone=%s';

    public static function buildUserOrdersHistory(string $phone): string
    {
        return sprintf(self::adminDomain().self::USER_ORDERS_HISTORY_URL_TEMPLATE, $phone);
    }

    public static function buildUserStatsUrl(string $cityUrl, string $phone): string
    {
        return sprintf(self::adminDomain().self::USER_STATS_URL_TEMPLATE, $cityUrl, $phone);
    }

    private static function adminDomain(): string
    {
        return 'https://'.config('services.admin.host');
    }
}
