<?php

namespace WezanEnterprises\LaravelAnalytics\src;

use Google\Analytics\Data\V1beta\{OrderBy\DimensionOrderBy, OrderBy\MetricOrderBy, OrderBy as GoogleOrderBy};

/**
 * Class OrderBy
 *
 * Provides methods for creating OrderBy objects for dimension and metric ordering.
 *
 * @package WezanEnterprises\LaravelAnalytics\src
 */
class OrderBy
{
    /**
     * Create an OrderBy object for dimension ordering.
     *
     * @param string $dimension  The name of the dimension to order by.
     * @param bool   $descending Whether to sort in descending order (default is ascending).
     *
     * @return GoogleOrderBy      An OrderBy object for dimension ordering.
     */
    public static function dimension(string $dimension, bool $descending = false): GoogleOrderBy
    {
        return (new GoogleOrderBy())->setDimension((new DimensionOrderBy())->setDimensionName($dimension))->setDesc($descending);
    }

    /**
     * Create an OrderBy object for metric ordering.
     *
     * @param string $metric     The name of the metric to order by.
     * @param bool   $descending Whether to sort in descending order (default is ascending).
     *
     * @return GoogleOrderBy      An OrderBy object for metric ordering.
     */
    public static function metric(string $metric, bool $descending = false): GoogleOrderBy
    {
        return (new GoogleOrderBy())->setMetric((new MetricOrderBy())->setMetricName($metric))->setDesc($descending);
    }
}