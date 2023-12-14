<?php

namespace WezanEnterprises\LaravelAnalytics;

use Google\Analytics\Data\V1beta\Row;
use Google\ApiCore\ApiException;
use GuzzleHttp\Exception\GuzzleException;
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
     * @param BetaAnalyticsClient|GoogleApiClient $client
     *
     * @throws InvalidInitializationException
     * @throws ApiException
     * @throws GuzzleException
     *
     * @return Collection
     */
    public function runBatchReports(BetaAnalyticsClient|GoogleApiClient $client): Collection
    {
        // Check if $this->reports is uninitialized
        if (!$this->initialized) {
            throw new InvalidInitializationException(__('No reports have been added to the batch.'));
        }

        $batchedResults = collect();

        if ($client instanceof BetaAnalyticsClient) {
            foreach ($client->runBatchReports($this->property, $this->reports)->getReports() as $reportIndex => $report) {
                $result = collect([
                    'rows' => collect(),
                    'metricAggregations' => collect(),
                    'rowCount' => null,
                    'totalRowCount' => null,
                    'metadata' => collect()
                ]);

                foreach ($this->reports[$reportIndex]->periods as $dateRangeIndex => $period) {
                    $result['rows'][$dateRangeIndex] = collect();
                }

                /** @var Row $row */
                foreach ($report->getRows() as $row) {
                    $dateRangeIndex ??= 0;
                    $rowResult = [];

                    foreach ($row->getDimensionValues() as $rowIndex => $dimensionValue) {
                        if ((count($this->reports[$reportIndex]->periods) > 1) && $rowIndex === count($row->getDimensionValues()) - 1) {
                            $dateRangeIndex = (int) substr($dimensionValue->getValue(), - 1);
                            break;
                        }

                        $rowResult[$this->reports[$reportIndex]->dimensions[$rowIndex]] = Formatter::castValue($this->reports[$reportIndex]->dimensions[$rowIndex], $dimensionValue->getValue());
                    }

                    foreach ($row->getMetricValues() as $rowIndex => $metricValue) {
                        $rowResult[$this->reports[$reportIndex]->metrics[$rowIndex]] = Formatter::castValue($this->reports[$reportIndex]->metrics[$rowIndex], $metricValue->getValue());
                    }

                    $result['rows'][$dateRangeIndex]->push($rowResult);
                }

                $result['rowCount'] = count($report->getRows());
                $result['totalRowCount'] = $report->getRowCount();
                $result['metadata'] = [
                    'currencyCode' => ($metadata = $report->getMetadata())->getCurrencyCode(),
                    'timeZone' => $metadata->getTimeZone(),
                    'subjectToThresholding' => $metadata->getSubjectToThresholding()
                ];

                if ((!empty($this->reports[$reportIndex]->metricAggregations)) && $report->getRowCount() > 0) {
                    $rowResult = [];

                    foreach ($this->reports[$reportIndex]->metrics as $i => $metric) {
                        foreach ($this->reports[$reportIndex]->metricAggregations as $metricAggregation) {
                            $rowResult[$metric][$metricAggregation] = match ($metricAggregation) {
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
        }
        else {
            foreach ($client->runBatchReports($this->property, $this->reports)['reports'] as $reportIndex => $report) {
                $result = collect([
                    'rows' => collect(),
                    'metricAggregations' => collect(),
                    'rowCount' => null,
                    'totalRowCount' => null
                ]);

                if (isset($report['rows'])) {
                    foreach ($report['rows'] as $row) {
                        $rowResult = [];

                        foreach ($row['dimensionValues'] as $rowIndex => $dimensionValue) {
                            $rowResult[$this->reports[$reportIndex]->dimensions[$rowIndex]] = Formatter::castValue($this->reports[$reportIndex]->dimensions[$rowIndex], $dimensionValue['value']);
                        }

                        foreach ($row['metricValues'] as $rowIndex => $metricValue) {
                            $rowResult[$this->reports[$reportIndex]->metrics[$rowIndex]] = Formatter::castValue($this->reports[$reportIndex]->metrics[$rowIndex], $metricValue['value']);
                        }

                        $result['rows']->push($rowResult);
                    }
                }

                $result['rowCount'] = count($report['rows']);
                $result['totalRowCount'] = $report['rowCount'];
                $result['metadata'] = $report['metadata'];

                if ((!empty($this->metricAggregations)) && $result['totalRowCount'] > 0) {
                    $rowResult = [];

                    foreach ($this->reports[$reportIndex]->metrics as $i => $metric) {
                        foreach ($this->reports[$reportIndex]->metricAggregations as $metricAggregation) {
                            $rowResult[$metric][$metricAggregation] = match ($metricAggregation) {
                                'TOTAL' => $result['totals'][0]['metricValues'][$i]['value'],
                                'MINIMUM' => $result['minimums'][0]['metricValues'][$i]['value'],
                                'MAXIMUM' => $result['maximums'][0]['metricValues'][$i]['value']
                            };
                        }
                    }

                    $result['metricAggregations']->push($rowResult);
                }

                $batchedResults->put($reportIndex, $result);
            }
        }

        return $batchedResults;
    }
}