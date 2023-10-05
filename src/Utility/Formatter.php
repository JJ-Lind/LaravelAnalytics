<?php

namespace WezanEnterprises\LaravelAnalytics\src;

use Google\Analytics\Data\V1beta\{Dimension, Metric};

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
        return collect($metrics)->map(fn(array $metric) => new Metric(['name' => $metric]))->toArray();
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
        return collect($dimensions)->map(fn(array $dimension) => new Dimension(['name' => $dimension]))->toArray();
    }
}