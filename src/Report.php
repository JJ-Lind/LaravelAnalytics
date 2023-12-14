<?php

namespace WezanEnterprises\LaravelAnalytics;

use Exception;
use Google\Analytics\Data\V1beta\Row;
use Google\ApiCore\ApiException;
use GuzzleHttp\Exception\GuzzleException;
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
    public array $periods;
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
     * @param Period[] $periods            The date_ranges for which to fetch data.
     * @param string[] $dimensions         The dimensions to include in the report.
     * @param int      $limit              The maximum number of results to return.
     * @param string[] $orderBy            The order in which to return results.
     * @param string[] $metricAggregations The metrics in which to return aggregated results.
     * @param int      $offset             The offset for pagination.
     * @param bool     $keepEmptyRows      Whether to keep empty rows in the result.
     *
     * @throws Exception
     */
    public function __construct(string $propertyId, array $metrics, array $periods, array $dimensions = [], int $limit = 10, array $orderBy = [], array $metricAggregations = [], int $offset = 0, bool $keepEmptyRows = false)
    {
        if (!is_null($errors = Validator::validateReport($propertyId, $metrics, $dimensions, $limit, $orderBy, $metricAggregations, $offset, $keepEmptyRows))) {
            throw new InvalidInitializationException($errors);
        }

        $this->propertyId = $propertyId;
        $this->metrics = $metrics;
        $this->periods = $periods;
        $this->dimensions = $dimensions;
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
     * @param BetaAnalyticsClient|GoogleApiClient $client
     *
     * @throws InvalidInitializationException
     * @throws ApiException
     * @throws GuzzleException
     * @return Collection
     */
    public function runReport(BetaAnalyticsClient|GoogleApiClient $client): Collection
    {
        $result = collect([
            'rows' => collect(),
            'metricAggregations' => collect(),
            'rowCount' => 0,
            'totalRowCount' => 0,
            'metadata' => collect()
        ]);

        foreach ($this->periods as $dateRangeIndex => $period) {
            $result['rows'][$dateRangeIndex] = collect();
        }

        // Check if $this->requestFields is uninitialized
        if (!$this->initialized) {
            throw new InvalidInitializationException(__('Request fields have not been initialized. Please call prepareReport() before calling runReport().'));
        }

        if ($client instanceof BetaAnalyticsClient) {
            /** @var Row $row */
            foreach (($reportResult = $client->runReport($this))->getRows() as $row) {
                $dateRangeIndex ??= 0;
                $rowResult = [];

                foreach ($row->getDimensionValues() as $i => $dimensionValue) {
                    if ((count($this->periods) > 1) && $i === count($row->getDimensionValues()) - 1) {
                        $dateRangeIndex = (int) substr($dimensionValue->getValue(), - 1);
                        break;
                    }

                    $rowResult[$this->dimensions[$i]] = Formatter::castValue($this->dimensions[$i], $dimensionValue->getValue());
                }

                foreach ($row->getMetricValues() as $i => $metricValue) {
                    $rowResult[$this->metrics[$i]] = Formatter::castValue($this->metrics[$i], $metricValue->getValue());
                }

                $result['rows'][$dateRangeIndex]->push($rowResult);
            }

            $result['rowCount'] = count($reportResult->getRows());
            $result['totalRowCount'] = $reportResult->getRowCount();
            $result['metadata'] = [
                'currencyCode' => ($metadata = $reportResult->getMetadata())->getCurrencyCode(),
                'timeZone' => $metadata->getTimeZone(),
                'subjectToThresholding' => $metadata->getSubjectToThresholding()
            ];

            if ((!empty($this->metricAggregations)) && $reportResult->getRowCount() > 0) {
                $rowResult = [];

                foreach ($this->metrics as $i => $metric) {
                    foreach ($this->metricAggregations as $metricAggregation) {
                        $rowResult[$metric][$metricAggregation] = match ($metricAggregation) {
                            'TOTAL' => $reportResult->getTotals()[0]->getMetricValues()[$i]->getValue(),
                            'MINIMUM' => $reportResult->getMinimums()[0]->getMetricValues()[$i]->getValue(),
                            'MAXIMUM' => $reportResult->getMaximums()[0]->getMetricValues()[$i]->getValue()
                        };
                    }
                }

                $result['metricAggregations']->push($rowResult);
            }
        }
        else {
            /** @var GoogleApiClient $client */
            $reportResult = $client->runReport($this);

            if (isset($reportResult['rows'])) {
                foreach ($reportResult['rows'] as $row) {
                    $rowResult = [];

                    foreach ($row['dimensionValues'] as $i => $dimensionValue) {
                        $rowResult[$this->dimensions[$i]] = Formatter::castValue($this->dimensions[$i], $dimensionValue['value']);
                    }

                    foreach ($row['metricValues'] as $i => $metricValue) {
                        $rowResult[$this->metrics[$i]] = Formatter::castValue($this->metrics[$i], $metricValue['value']);
                    }

                    $result['rows']->push($rowResult);
                }

                $result['rowCount'] = count($result['rows']);
                $result['totalRowCount'] = $reportResult['rowCount'];
                $result['metadata'] = $reportResult['metadata'];
            }

            if ((!empty($this->metricAggregations)) && $result['totalRowCount'] > 0) {
                $rowResult = [];

                foreach ($this->metrics as $i => $metric) {
                    foreach ($this->metricAggregations as $metricAggregation) {
                        $rowResult[$metric][$metricAggregation] = match ($metricAggregation) {
                            'TOTAL' => $reportResult['totals'][0]['metricValues'][$i]['value'],
                            'MINIMUM' => $reportResult['minimums'][0]['metricValues'][$i]['value'],
                            'MAXIMUM' => $reportResult['maximums'][0]['metricValues'][$i]['value']
                        };
                    }
                }

                $result['metricAggregations']->push($rowResult);
            }
        }

        return $result;
    }
}