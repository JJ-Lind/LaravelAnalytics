<?php

namespace WezanEnterprises\LaravelAnalytics\src;

use Google\Analytics\Data\V1beta\FilterExpression;
use Google\ApiCore\ApiException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use WezanEnterprises\LaravelAnalytics\src\Utility\Formatter;
use WezanEnterprises\LaravelAnalytics\src\Utility\Period;
use WezanEnterprises\LaravelAnalytics\src\Utility\Validator;

class Report extends Client {

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
     * @throws ValidationException
     *
     * @return Collection
     */
    public function prepareReport(string $propertyId, Period $period, array $metrics, array $dimensions = [], int $maxResults = 10, array $orderBy = [], int $offset = 0, FilterExpression $dimensionFilter = null, bool $keepEmptyRows = false): Collection
    {
        Validator::validate($propertyId, $metrics, $dimensions, $maxResults, $orderBy, $offset, $keepEmptyRows);

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

    public function getEstablishedUsers()
    {

    }

    public function getUnEstablishedUsers()
    {

    }

    public function getTotalUsers()
    {

    }

    public function getRoas()
    {

    }
}