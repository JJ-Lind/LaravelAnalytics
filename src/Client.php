<?php

namespace WezanEnterprises\LaravelAnalytics\src\Client;

use Google\Analytics\Data\V1beta\{BetaAnalyticsDataClient, Dimension, FilterExpression, Metric};
use Google\ApiCore\{ApiException, ValidationException};
use Illuminate\Support\{Carbon, Collection};
use WezanEnterprises\LaravelAnalytics\src\{Exceptions\InvalidPeriodException, Formatter, Period\Period};

/**
 * Class Client
 *
 * This class provides a client for fetching analytics data using Google Analytics Data API.
 */
class Client {

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
     * Fetch analytics data for a property within a specified period.
     *
     * @param string                $propertyId      The ID of the property to fetch data for.
     * @param Period                $period          The period for which to fetch data.
     * @param string[]              $metrics         The metrics to include in the report.
     * @param string[]              $dimensions      The dimensions to include in the report.
     * @param int                   $maxResults      The maximum number of results to return.
     * @param string[]              $orderBy         The order in which to return results.
     * @param int                   $offset          The offset for pagination.
     * @param FilterExpression|null $dimensionFilter A filter expression for dimensions.
     * @param bool                  $keepEmptyRows   Whether to keep empty rows in the result.
     *
     * @throws ApiException
     *
     * @return Collection
     */
    public function get(string $propertyId, Period $period, array $metrics, array $dimensions = [], int $maxResults = 10, array $orderBy = [], int $offset = 0, FilterExpression $dimensionFilter = null, bool $keepEmptyRows = false): Collection
    {
        $result = collect();

        foreach ($this->client->runReport([
            'property' => "properties/$propertyId",
            'dateRanges' => [
                $period->getDateRange(),
            ],
            'metrics' => Formatter::formatMetrics($metrics),
            'dimensions' => Formatter::formatDimensions($dimensions),
            'limit' => $maxResults,
            'offset' => $offset,
            'orderBys' => $orderBy,
            'dimensionFilter' => $dimensionFilter,
            'keepEmptyRows' => $keepEmptyRows
        ])->getRows() as $row) {
            $rowResult = [];

            foreach ($row->getDimensionValues() as $i => $dimensionValue) {
                $rowResult[$dimensions[$i]] = self::castValue($dimensions[$i], $dimensionValue->getValue());
            }

            foreach ($row->getMetricValues() as $i => $metricValue) {
                $rowResult[$metrics[$i]] = self::castValue($metrics[$i], $metricValue->getValue());
            }

            $result->push($rowResult);
        }

        return $result;
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
            'visitors', 'pageViews', 'activeUsers', 'newUsers', 'screenPageViews', 'active1DayUsers', 'active7DayUsers', 'active28DayUsers' => (int) $value,
            default => $value,
        };
    }
}