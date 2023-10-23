<?php

namespace WezanEnterprises\LaravelAnalytics;

use Google\Analytics\Data\V1beta\{Dimension, Metric, RunReportRequest};
use Illuminate\Support\Carbon;

class Formatter {

    /**
     * Format metrics for API request.
     *
     * @param array $metrics The metrics to format.
     *
     * @return array
     */
    public static function formatMetrics(array $metrics): array
    {
        return collect($metrics)->map(fn(string $metric) => new Metric(['name' => $metric]))->toArray();
    }

    /**
     * Format dimensions for API request.
     *
     * @param array $dimensions The dimensions to format.
     *
     * @return array
     */
    public static function formatDimensions(array $dimensions): array
    {
        return collect($dimensions)->map(fn(string $dimension) => new Dimension(['name' => $dimension]))->toArray();
    }

    public static function formatReportRequest(Report $report): RunReportRequest
    {
        return new RunReportRequest([
            'property' => "properties/$report->propertyId",
            'metrics' => $report->metrics,
            'date_ranges' => [$report->period->getDateRange()],
            'dimensions' => $report->dimensions,
            'limit' => $report->limit,
            'offset' => $report->offset,
            'order_bys' => $report->orderBy,
            'keep_empty_rows' => $report->keepEmptyRows
        ]);
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