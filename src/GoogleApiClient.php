<?php

namespace WezanEnterprises\LaravelAnalytics;

use Exception;
use Google\Client;
use GuzzleHttp\{Client as GuzzleClient, Exception\GuzzleException};

class GoogleApiClient implements AnalyticsClientInterface {

    public const SCOPE_ANALYZE = 'https://www.googleapis.com/auth/analytics';
    public const SCOPE_READ = 'https://www.googleapis.com/auth/analytics.readonly';

    /**
     * The instance of the Google API Client.
     *
     * @var Client
     */
    protected Client $googleClient;

    /**
     * The instance of the Guzzle HTTP Client.
     *
     * @var GuzzleClient
     */
    protected GuzzleClient $guzzleClient;

    /**
     * Client constructor.
     *
     * @param Client $googleClient
     *
     * @throws Exception
     */
    public function __construct(Client $googleClient)
    {
        // Ensure access token is not null
        if (is_null($googleClient->getAccessToken())) {
            throw new Exception("Access token is null. Make sure to authenticate and obtain a valid access token.");
        }

        $this->guzzleClient = new GuzzleClient();
        $this->googleClient = $googleClient;
    }

    /**
     * Run a report request.
     *
     * @param Report $runReportRequest The report request to run.
     *
     * @throws GuzzleException
     * @return array The response data as an array.
     */
    public function runReport(Report $runReportRequest): array
    {
        return json_decode($this->guzzleClient->post("https://analyticsdata.googleapis.com/v1beta/properties/$runReportRequest->propertyId:runReport", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->googleClient->getAccessToken()['access_token'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ],
            'json' => Formatter::formatReportRequest($runReportRequest, $this->googleClient),
        ])->getBody(), true);
    }

    /**
     * Run batch reports for a specified property.
     *
     * @param string $propertyId        The ID of the property for which to run reports.
     * @param array  $runReportRequests An array of report requests to run in the batch.
     *
     * @throws GuzzleException
     * @return array The response data as an array.
     */
    public function runBatchReports(string $propertyId, array $runReportRequests): array
    {
        return json_decode($this->guzzleClient->post("https://analyticsdata.googleapis.com/v1beta/properties/$propertyId:batchRunReports", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->googleClient->getAccessToken()['access_token'],
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'property' => $propertyId,
                'requests' => array_map(function (Report $runReportRequest) {
                    return Formatter::formatBatchReportRequest($runReportRequest, $this->googleClient);
                }, $runReportRequests),
            ],
        ])->getBody(), true);
    }
}
