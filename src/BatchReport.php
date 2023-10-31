<?php

namespace WezanEnterprises\LaravelAnalytics;

use Google\Analytics\Data\V1beta\Row;
use Google\ApiCore\ApiException;
use Illuminate\Support\Collection;
use WezanEnterprises\LaravelAnalytics\Exceptions\InvalidInitializationException;

/**
 * Class Report
 *
 * This class provides an extension of the Google Analytics Data API client by batching multiple report requests into a single request.
 *
 * @package WezanEnterprises\LaravelAnalytics
 */
class BatchReport {

    public string $property;
    public array $reports;
    protected bool $initialized = false;

    /**
     * @param string $property The ID of the property to fetch data for.
     * @param array  $reports  [Report] An array of Report objects used to fetch data for.
     *
     * @throws InvalidInitializationException
     */
    public function __construct(string $property, array $reports)
    {
        if (!is_null($errors = Validator::validateBatchReports($property, $reports))) {
            throw new InvalidInitializationException($errors);
        }

        $this->property = $property;
        $this->reports = $reports;

        $this->initialized = true;
    }

    /**
     * Fetches analytics data for a batch of reports within a specified period.
     *
     * @param Client $client
     *
     * @throws InvalidInitializationException
     * @throws ApiException
     *
     * @return Collection
     */
    public function runBatchReports(Client $client): Collection
    {
        // Check if $this->reports is uninitialized
        if (!$this->initialized) {
            throw new InvalidInitializationException(__('No reports have been added to the batch.'));
        }

        $batchedResults = collect();

        foreach ($client->runBatchReports($this->property, $this->reports)->getReports() as $reportIndex => $report) {
            $result = collect([
                'rows' => collect(),
                'metricAggregations' => collect(),
                'rowCount' => null,
                'totalRowCount' => null
            ]);

            /** @var Row $row */
            foreach ($report->getRows() as $row) {
                $rowResult = [];

                foreach ($row->getDimensionValues() as $rowIndex => $dimensionValue) {
                    $rowResult[$this->reports[$reportIndex]->dimensions[$rowIndex]->getName()] = Formatter::castValue($this->reports[$reportIndex]->dimensions[$rowIndex]->getName(), $dimensionValue->getValue());
                }

                foreach ($row->getMetricValues() as $rowIndex => $metricValue) {
                    $rowResult[$this->reports[$reportIndex]->metrics[$rowIndex]->getName()] = Formatter::castValue($this->reports[$reportIndex]->metrics[$rowIndex]->getName(), $metricValue->getValue());
                }

                $result['rows']->push($rowResult);
            }

            $result['rowCount'] = $result['rows']->count();
            $result['totalRowCount'] = $report->getRowCount();

            if (!empty($this->reports[$reportIndex]->metricAggregations)) {
                $rowResult = [];

                foreach ($this->reports[$reportIndex]->metrics as $i => $metric) {
                    foreach ($this->reports[$reportIndex]->metricAggregations as $metricAggregation) {
                        $rowResult[$metric->getName()][$metricAggregation] = match ($metricAggregation) {
                            'TOTAL' => $report->getTotals()[0]->getMetricValues()[$i]->getValue(),
                            'MINIMUM' => $report->getMinimums()[0]->getMetricValues()[$i]->getValue(),
                            'MAXIMUM' => $report->getMaximums()[0]->getMetricValues()[$i]->getValue()
                        };
                    }
                }

                $result['metricAggregations']->push($rowResult);
            }

            $batchedResults->put($reportIndex, $result);
        }

        return $batchedResults;
    }
}