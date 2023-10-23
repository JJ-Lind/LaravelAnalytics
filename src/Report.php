<?php

namespace WezanEnterprises\LaravelAnalytics;

use Exception;
use Google\ApiCore\ApiException;
use Illuminate\Support\Collection;
use WezanEnterprises\LaravelAnalytics\Exceptions\InvalidInitializationException;

/**
 * Class Report
 */
class Report {

    public string $propertyId;
    public array $metrics;
    public Period $period;
    public array $dimensions;
    public int $limit;
    public array $orderBy;
    public int $offset;
    public bool $keepEmptyRows;
    private bool $initialized = false;

    /**
     * @param string   $propertyId   The ID of the property to fetch data for.
     * @param string[] $metrics      The metrics to include in the report.
     * @param Period   $period       The date_ranges for which to fetch data.
     * @param string[] $dimensions   The dimensions to include in the report.
     * @param int      $limit        The maximum number of results to return.
     * @param string[] $orderBy      The order in which to return results.
     * @param int      $offset       The offset for pagination.
     * @param bool     $keepEmptyRows Whether to keep empty rows in the result.
     *
     * @throws Exception
     */
    public function __construct(string $propertyId, array $metrics, Period $period, array $dimensions = [], int $limit = 10, array $orderBy = [], int $offset = 0, bool $keepEmptyRows = false)
    {
        if (!is_null($errors = Validator::validateReport($propertyId, $metrics, $dimensions, $limit, $orderBy, $offset, $keepEmptyRows))) {
            throw new InvalidInitializationException($errors);
        }

        $this->propertyId = $propertyId;
        $this->metrics = Formatter::formatMetrics($metrics);
        $this->period = $period;
        $this->dimensions = Formatter::formatDimensions($dimensions);
        $this->limit = $limit;
        $this->orderBy = $orderBy;
        $this->offset = $offset;
        $this->keepEmptyRows = $keepEmptyRows;

        $this->initialized = true;
    }

    /**
     * Fetches analytics data for a property within a specified date_ranges.
     *
     * @param Client $client
     *
     * @throws InvalidInitializationException
     * @throws ApiException
     * @return Collection
     */
    public function runReport(Client $client): Collection
    {
        $result = collect();

        // Check if $this->requestFields is uninitialized
        if (!$this->initialized) {
            throw new InvalidInitializationException(__('Request fields have not been initialized. Please call prepareReport() before calling runReport().'));
        }

        foreach ($client->runReport([
            'property' => "properties/$this->propertyId",
            'date_ranges' => [$this->period->getDateRange()],
            'metrics' => $this->metrics,
            'dimensions' => $this->dimensions,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'order_by' => $this->orderBy,
            'keep_empty_rows' => $this->keepEmptyRows
        ])->getRows() as $row) {
            $rowResult = [];

            foreach ($row->getDimensionValues() as $i => $dimensionValue) {
                $rowResult[$this->dimensions[$i]->getName()] = Formatter::castValue($this->dimensions[$i]->getName(), $dimensionValue->getValue());
            }

            foreach ($row->getMetricValues() as $i => $metricValue) {
                $rowResult[$this->metrics[$i]->getName()] = Formatter::castValue($this->metrics[$i]->getName(), $metricValue->getValue());
            }

            $result->push($rowResult);
        }

        return $result;
    }
}