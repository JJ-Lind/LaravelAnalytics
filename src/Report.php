<?php

namespace WezanEnterprises\LaravelAnalytics;

use Exception;
use Google\ApiCore\ApiException;
use Illuminate\Support\Collection;
use WezanEnterprises\LaravelAnalytics\Exceptions\InvalidInitializationException;

/**
 * Class Report
 *
 * This class provides an extension of the Client for fetching analytics data using Google Analytics Data API's report endpoint.
 */
final class Report extends Client {

    /**
     * @var array Holds the request fields for the report.
     */
    private array $requestFields;

    /**
     * Prepares the report request.
     *
     * @param string      $propertyId    The ID of the property to fetch data for.
     * @param string[]    $metrics       The metrics to include in the report.
     * @param Period|null $period        The period for which to fetch data (optional).
     * @param string[]    $dimensions    The dimensions to include in the report.
     * @param int         $maxResults    The maximum number of results to return.
     * @param string[]    $orderBy       The order in which to return results.
     * @param int         $offset        The offset for pagination.
     * @param bool        $keepEmptyRows Whether to keep empty rows in the result.
     *
     * @throws Exception
     *
     * @return $this
     */
    public function prepareReport(string $propertyId, array $metrics, Period $period = null, array $dimensions = [], int $maxResults = 10, array $orderBy = [], int $offset = 0, bool $keepEmptyRows = false): self
    {
        if (!is_null($errors = Validator::validate($propertyId, $metrics, $dimensions, $maxResults, $orderBy, $offset, $keepEmptyRows))) {
            throw new InvalidInitializationException($errors);
        }

        $this->requestFields = [
            'property' => "properties/$propertyId",
            'dateRanges' => $period
                ? [$period->getDateRange()]
                : [],
            'metrics' => Formatter::formatMetrics($metrics),
            'dimensions' => Formatter::formatDimensions($dimensions),
            'limit' => $maxResults,
            'offset' => $offset,
            'orderBys' => $orderBy,
            'keepEmptyRows' => $keepEmptyRows
        ];

        return $this;
    }

    /**
     * Fetches analytics data for a property within a specified period.
     *
     * @throws ApiException
     * @throws Exception
     *
     * @return Collection
     */
    public function runReport(): Collection
    {
        $result = collect();

        // Check if $this->requestFields is uninitialized
        if (empty($this->requestFields)) {
            throw new InvalidInitializationException(__('Request fields have not been initialized. Please call prepareReport() before calling runReport().'));
        }

        foreach ($this->client->runReport($this->requestFields)->getRows() as $row) {
            $rowResult = [];

            foreach ($row->getDimensionValues() as $i => $dimensionValue) {
                $rowResult[$this->requestFields['dimensions'][$i]->getName()] = $this->castValue($this->requestFields['dimensions'][$i]->getName(), $dimensionValue->getValue());
            }

            foreach ($row->getMetricValues() as $i => $metricValue) {
                $rowResult[$this->requestFields['metrics'][$i]->getName()] = $this->castValue($this->requestFields['metrics'][$i]->getName(), $metricValue->getValue());
            }

            $result->push($rowResult);
        }

        return $result;
    }
}