<?php

namespace WezanEnterprises\LaravelAnalytics;

use Google\Analytics\Data\V1beta\{BatchRunReportsResponse, BetaAnalyticsDataClient, RunReportResponse};
use Google\ApiCore\{ApiException, ValidationException};

class BetaAnalyticsClient implements AnalyticsClientInterface {

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
     * Runs a single report request.
     *
     * @param Report $runReportRequest The report request to run.
     *
     * @throws ApiException
     *
     * @return RunReportResponse The response containing the report data.
     */
    public function runReport(Report $runReportRequest): RunReportResponse
    {
        return $this->client->runReport(Formatter::formatReportRequest($runReportRequest, $this->client));
    }

    /**
     * Runs multiple report requests in a batch for a specified property.
     *
     * @param string $propertyId        The ID of the property for which to run reports.
     * @param array  $runReportRequests An array of report requests to run in the batch.
     *
     * @throws ApiException
     *
     * @return BatchRunReportsResponse The response containing batched report data.
     */
    public function runBatchReports(string $propertyId, array $runReportRequests): BatchRunReportsResponse
    {
        return $this->client->batchRunReports([
            'property' => "properties/$propertyId",
            'requests' => array_map(function (Report $runReportRequest) {
                return Formatter::formatBatchReportRequest($runReportRequest, $this->client);
            }, $runReportRequests)
        ]);
    }
}