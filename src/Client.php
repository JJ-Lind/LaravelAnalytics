<?php

namespace WezanEnterprises\LaravelAnalytics;

use Google\{Analytics\Data\V1beta\BatchRunReportsResponse, Analytics\Data\V1beta\BetaAnalyticsDataClient, Analytics\Data\V1beta\RunReportResponse, ApiCore\ApiException, ApiCore\ValidationException};

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
            $clientData['credentials'] = config('analytics.service_account_credentials_json', storage_path('app/analytics/service-account-credentials.json'));
        }

        $this->client = new BetaAnalyticsDataClient($clientData);
    }

    /**
     * @throws ApiException
     */
    public function runReport(array $runReportRequest): RunReportResponse
    {
        return $this->client->runReport($runReportRequest);
    }

    /**
     * @throws ApiException
     */
    public function runBatchReports(string $propertyId, array $runReportRequests): BatchRunReportsResponse
    {
        return $this->client->batchRunReports([
            'property' => "properties/$propertyId",
            'requests' => array_map(function (Report $runReportRequest) {
                return Formatter::formatReportRequest($runReportRequest);
            }, $runReportRequests)
        ]);
    }
}