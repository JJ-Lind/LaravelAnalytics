<?php

namespace WezanEnterprises\LaravelAnalytics;

use Google\ApiCore\ApiException;
use Illuminate\Support\Collection;
use WezanEnterprises\LaravelAnalytics\Exceptions\InvalidInitializationException;

/**
 * Class Report
 *
 * This class provides an extension of the Google Analytics Data API client by batching multiple report requests into a single request.
 */
class BatchReport {

    public string $property;
    public array $reports;
    private bool $rowIndexnitialized = false;

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

        $result = collect();

        foreach ($client->runBatchReports($this->property, $this->reports)->getReports() as $reportIndex => $report) {
            $reportResult = collect();

            foreach ($report->getRows() as $row) {
                $rowResult = [];

                foreach ($row->getDimensionValues() as $rowIndex => $dimensionValue) {
                    $rowResult[$this->reports[$reportIndex]->dimensions[$rowIndex]->getName()] = Formatter::castValue($this->reports[$reportIndex]->dimensions[$rowIndex]->getName(), $dimensionValue->getValue());
                }

                foreach ($row->getMetricValues() as $rowIndex => $metricValue) {
                    $rowResult[$this->reports[$reportIndex]->metrics[$rowIndex]->getName()] = Formatter::castValue($this->reports[$reportIndex]->metrics[$rowIndex]->getName(), $metricValue->getValue());
                }

                $reportResult->push($rowResult);
            }

            $result->put($reportIndex, $reportResult);
        }

        return $result;
    }
}