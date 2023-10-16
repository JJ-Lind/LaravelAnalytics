<?php

namespace WezanEnterprises\LaravelAnalytics;

use Google\{Analytics\Data\V1beta\BetaAnalyticsDataClient, ApiCore\ValidationException};
use Illuminate\Support\Carbon;

/**
 * Class Client
 *
 * This class provides a client for fetching analytics data using Google Analytics Data API.
 */
class Client {

    /**
     * The instance of the Google Analytics Data API client.
     *
     * @var BetaAnalyticsDataClient
     */
    protected BetaAnalyticsDataClient $client;

    /**
     * Client constructor.
     *
     * @param array $clientData Configuration data for the client.
     *
     * @throws ValidationException
     */
    public function __construct(array $clientData = [])
    {
        if (!isset($clientData['credentials'])) {
            $clientData['credentials'] = storage_path('app/analytics/service-account-credentials.json');
        }

        $this->client = new BetaAnalyticsDataClient($clientData);
    }

    /**
     * Cast a value based on the dimension or metric key.
     *
     * @param string $key   The key of the dimension or metric.
     * @param string $value The value to cast.
     *
     * @return string|int|bool|Carbon
     */
    protected function castValue(string $key, string $value): string|int|bool|Carbon
    {
        return match ($key) {
            'date' => Carbon::createFromFormat('Ymd', $value),
            'visitors', 'pageViews', 'activeUsers', 'newUsers', 'screenPageViews', 'active1DayUsers', 'active7DayUsers', 'active28DayUsers', 'totalUsers' => (int) $value,
            default => $value
        };
    }
}