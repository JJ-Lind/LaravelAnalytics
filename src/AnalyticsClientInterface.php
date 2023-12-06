<?php

namespace WezanEnterprises\LaravelAnalytics;

interface AnalyticsClientInterface {

    public function runReport(Report $runReportRequest);

    public function runBatchReports(string $propertyId, array $runReportRequests);
}
