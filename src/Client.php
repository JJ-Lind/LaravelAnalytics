<?php

namespace WezanEnterprises\LaravelAnalytics;

use Exception;
use Google\{Analytics\Data\V1beta\BetaAnalyticsDataClient, ApiCore\ValidationException};
use Google_Client;

/**
 * Class Client
 *
 * This class provides a client for fetching analytics data using Google Analytics Data API.
 *
 * @package WezanEnterprises\LaravelAnalytics
 */
class Client {

    /**
     * The instance of the Google Analytics Data API client.
     *
     * @var Google_Client|BetaAnalyticsDataClient
     */
    protected Google_Client|BetaAnalyticsDataClient $client;

    /**
     * Client constructor.
     *
     * @param Google_Client|array|null $client Configuration data for the client.
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function __construct(Google_Client|array $client = null)
    {

    }
}
