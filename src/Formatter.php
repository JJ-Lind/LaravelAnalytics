<?php

namespace WezanEnterprises\LaravelAnalytics;

use Google\Analytics\Data\V1beta\{BetaAnalyticsDataClient, Dimension, Metric, RunReportRequest};
use Illuminate\Support\Carbon;
use Google\Client;

/**
 * Class Formatter
 *
 * This class provides methods for formatting data for Google Analytics API requests.
 *
 * @package WezanEnterprises\LaravelAnalytics
 */
class Formatter {

    /**
     * Format metrics for API request.
     *
     * @param array $metrics The metrics to format.
     *
     * @return Metric[]|array
     */
    public static function formatMetrics(array $metrics, BetaAnalyticsDataClient|Client $client): array
    {
        return $client instanceof BetaAnalyticsDataClient
            ? collect($metrics)->map(fn(string $metric) => new Metric(['name' => $metric]))->toArray()
            : array_map(fn(string $metric) => ['name' => $metric], $metrics);
    }

    /**
     * Format dimensions for API request.
     *
     * @param array $dimensions The dimensions to format.
     *
     * @return Dimension[]|array
     */
    public static function formatDimensions(array $dimensions, BetaAnalyticsDataClient|Client $client): array
    {
        return $client instanceof BetaAnalyticsDataClient
            ? collect($dimensions)->map(fn(string $dimension) => new Dimension(['name' => $dimension]))->toArray()
            : array_map(fn(string $dimension) => ['name' => $dimension], $dimensions);
    }

    /**
     * Format a batch report request.
     *
     * @param Report $report The report to format.
     *
     * @return RunReportRequest
     */
    public static function formatBatchReportRequest(Report $report): RunReportRequest
    {
        return new RunReportRequest([
            'property' => "properties/$report->propertyId",
            'metrics' => $report->metrics,
            'date_ranges' => [$report->period->getDateRange()],
            'dimensions' => $report->dimensions,
            'limit' => $report->limit,
            'offset' => $report->offset,
            'metric_aggregations' => array_map(fn(string $metricAggregation) => self::getMetricAggregation($metricAggregation), $report->metricAggregations),
            'order_bys' => $report->orderBy,
            'keep_empty_rows' => $report->keepEmptyRows
        ]);
    }

    /**
     * Get the metric aggregation code.
     *
     * @param string $metricAggregation The metric aggregation type.
     *
     * @return int
     */
    private static function getMetricAggregation(string $metricAggregation): int
    {
        return match ($metricAggregation) {
            'TOTAL' => 1,
            'COUNT' => 4,
            'MINIMUM' => 5,
            'MAXIMUM' => 6,
            default => 0
        };
    }

    /**
     * Format a report request.
     *
     * @param Report                         $report The report to format.
     * @param BetaAnalyticsDataClient|Client $client
     *
     * @return array
     */
    public static function formatReportRequest(Report $report, BetaAnalyticsDataClient|Client $client): array
    {
        return [
            'property' => "properties/$report->propertyId",
            'dateRanges' => $client instanceof BetaAnalyticsDataClient
                ? [$report->period->getDateRange()]
                : [
                    [
                        'startDate' => $report->period->getStartDate()->format('Y-m-d'),
                        'endDate' => $report->period->getEndDate()->format('Y-m-d')
                    ]
                ],
            'metrics' => self::formatMetrics($report->metrics, $client),
            'dimensions' => self::formatDimensions($report->dimensions, $client),
            'limit' => $report->limit,
            'offset' => $report->offset,
            'metricAggregations' => array_map(fn(string $metricAggregation) => self::getMetricAggregation($metricAggregation), $report->metricAggregations),
            'orderBys' => $report->orderBy,
            'keepEmptyRows' => $report->keepEmptyRows
        ];
    }

    /**
     * Cast a value based on the dimension or metric key.
     *
     * @param string $key   The key of the dimension or metric.
     * @param string $value The value to cast.
     *
     * @return string|int|bool|Carbon
     */
    public static function castValue(string $key, string $value): string|int|bool|Carbon
    {
        return match ($key) {
            'date' => Carbon::createFromFormat('Ymd', $value),
            'visitors', 'pageViews', 'activeUsers', 'newUsers', 'screenPageViews', 'active1DayUsers', 'active7DayUsers', 'active28DayUsers', 'totalUsers' => (int) $value,
            default => $value
        };
    }
}
