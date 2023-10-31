<?php

namespace WezanEnterprises\LaravelAnalytics;

use Exception;
use Google\Analytics\Data\V1beta\Row;
use Google\ApiCore\ApiException;
use Illuminate\Support\Collection;
use WezanEnterprises\LaravelAnalytics\Exceptions\InvalidInitializationException;

/**
 * Class Report
 *
 * @package WezanEnterprises\LaravelAnalytics
 */
class Report {

    public string $propertyId;
    public array $metrics;
    public Period $period;
    public array $dimensions;
    public int $limit;
    public array $orderBy;
    public array $metricAggregations;
    public int $offset;
    public bool $keepEmptyRows;
    private bool $initialized = false;

    /**
     * @param string   $propertyId         The ID of the property to fetch data for.
     * @param string[] $metrics            The metrics to include in the report.
     * @param Period   $period             The date_ranges for which to fetch data.
     * @param string[] $dimensions         The dimensions to include in the report.
     * @param int      $limit              The maximum number of results to return.
     * @param string[] $orderBy            The order in which to return results.
     * @param string[] $metricAggregations The metrics in which to return aggregated results.
     * @param int      $offset             The offset for pagination.
     * @param bool     $keepEmptyRows      Whether to keep empty rows in the result.
     *
     * @throws Exception
     */
    public function __construct(string $propertyId, array $metrics, Period $period, array $dimensions = [], int $limit = 10, array $orderBy = [], array $metricAggregations = [], int $offset = 0, bool $keepEmptyRows = false)
    {
        if (!is_null($errors = Validator::validateReport($propertyId, $metrics, $dimensions, $limit, $orderBy, $metricAggregations, $offset, $keepEmptyRows))) {
            throw new InvalidInitializationException($errors);
        }

        $this->propertyId = $propertyId;
        $this->metrics = Formatter::formatMetrics($metrics);
        $this->period = $period;
        $this->dimensions = Formatter::formatDimensions($dimensions);
        $this->limit = $limit;
        $this->orderBy = $orderBy;
        $this->metricAggregations = $metricAggregations;
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
        $result = collect([
            'rows' => collect(),
            'metricAggregations' => collect(),
            'rowCount' => null,
            'totalRowCount' => null
        ]);

        // Check if $this->requestFields is uninitialized
        if (!$this->initialized) {
            throw new InvalidInitializationException(__('Request fields have not been initialized. Please call prepareReport() before calling runReport().'));
        }

        /** @var Row $row */
        foreach (($reportResult = $client->runReport($this))->getRows() as $row) {
            $rowResult = [];

            foreach ($row->getDimensionValues() as $i => $dimensionValue) {
                $rowResult[$this->dimensions[$i]->getName()] = Formatter::castValue($this->dimensions[$i]->getName(), $dimensionValue->getValue());
            }

            foreach ($row->getMetricValues() as $i => $metricValue) {
                $rowResult[$this->metrics[$i]->getName()] = Formatter::castValue($this->metrics[$i]->getName(), $metricValue->getValue());
            }

            $result['rows']->push($rowResult);
        }

        $result['rowCount'] = $result['rows']->count();
        $result['totalRowCount'] = $reportResult->getRowCount();

        if (!empty($this->metricAggregations)) {
            $rowResult = [];

            foreach ($this->metrics as $i => $metric) {
                foreach ($this->metricAggregations as $metricAggregation) {
                    $rowResult[$metric->getName()][$metricAggregation] = match ($metricAggregation) {
                        'TOTAL' => $reportResult->getTotals()[0]->getMetricValues()[$i]->getValue(),
                        'MINIMUM' => $reportResult->getMinimums()[0]->getMetricValues()[$i]->getValue(),
                        'MAXIMUM' => $reportResult->getMaximums()[0]->getMetricValues()[$i]->getValue()
                    };
                }
            }

            $result['metricAggregations']->push($rowResult);
        }

        return $result;
    }
}