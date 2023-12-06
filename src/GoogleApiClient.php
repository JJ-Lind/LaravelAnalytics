<?php

namespace WezanEnterprises\LaravelAnalytics;

use Exception;
use Google_Client;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

class GoogleApiClient implements AnalyticsClientInterface {

    public const SCOPE_ANALYZE = 'https://www.googleapis.com/auth/analytics';
    public const SCOPE_READ = 'https://www.googleapis.com/auth/analytics.readonly';
    /**
     * The instance of the Google API Client.
     *
     * @var Google_Client
     */
    protected Google_Client $googleClient;
    protected GuzzleClient $guzzleClient;

    /**
     * Client constructor.
     *
     * @param Google_Client $googleClient
     *
     * @throws Exception
     */
    public function __construct(Google_Client $googleClient)
    {
        // Ensure access token is not null
        if (is_null($googleClient->getAccessToken())) {
            throw new Exception("Access token is null. Make sure to authenticate and obtain a valid access token.");
        }

        $this->guzzleClient = new GuzzleClient();
        $this->googleClient = $googleClient;
    }

    /**
     * @param Report $runReportRequest
     *
     * @throws GuzzleException
     * @return array
     */
    public function runReport(Report $runReportRequest): array
    {
        return json_decode($this->guzzleClient->post("https://analyticsdata.googleapis.com/v1beta/properties/$runReportRequest->propertyId:runReport", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->googleClient->getAccessToken()['access_token'],
                'Content-Type' => 'application/json'
            ],
            'json' => Formatter::formatReportRequest($runReportRequest, $this->googleClient)
        ])->getBody(), true);
    }

    /**
     * @param string $propertyId
     * @param array  $runReportRequests
     *
     * @throws GuzzleException
     * @return array
     */
    public function runBatchReports(string $propertyId, array $runReportRequests): array
    {
        return json_decode($this->guzzleClient->post("https://analyticsdata.googleapis.com/v1beta/properties/$propertyId:batchRunReports", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->googleClient->getAccessToken()['access_token'],
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'property' => $propertyId,
                'requests' => array_map(function (Report $runReportRequest) {
                    return Formatter::formatBatchReportRequest($runReportRequest, $this->googleClient);
                }, $runReportRequests)
            ]
        ])->getBody(), true);
    }
}
