<?php

namespace WezanEnterprises\LaravelAnalytics;

use Illuminate\Support\Facades\Validator as LaravelValidator;
use Illuminate\Support\MessageBag;
use InvalidArgumentException;

class Validator {

    /**
     * The available metrics for validation.
     *
     * @var array
     */
    protected const AVAILABLE_METRICS = [
        "active1DayUsers",
        "active28DayUsers",
        "active7DayUsers",
        "activeUsers",
        "adUnitExposure",
        "addToCarts",
        "advertiserAdClicks",
        "advertiserAdCost",
        "advertiserAdCostPerClick",
        "advertiserAdCostPerConversion",
        "advertiserAdImpressions",
        "averagePurchaseRevenue",
        "averagePurchaseRevenuePerPayingUser",
        "averagePurchaseRevenuePerUser",
        "averageRevenuePerUser",
        "averageSessionDuration",
        "bounceRate",
        "cartToViewRate",
        "checkouts",
        "cohortActiveUsers",
        "cohortTotalUsers",
        "conversions",
        "crashAffectedUsers",
        "crashFreeUsersRate",
        "dauPerMau",
        "dauPerWau",
        "ecommercePurchases",
        "engagedSessions",
        "engagementRate",
        "eventCount",
        "eventCountPerUser",
        "eventValue",
        "eventsPerSession",
        "firstTimePurchaserConversionRate",
        "firstTimePurchasers",
        "firstTimePurchasersPerNewUser",
        "grossItemRevenue",
        "grossPurchaseRevenue",
        "itemDiscountAmount",
        "itemListClickEvents",
        "itemListClickThroughRate",
        "itemListViewEvents",
        "itemPromotionClickThroughRate",
        "itemRefundAmount",
        "itemRevenue",
        "itemViewEvents",
        "itemsAddedToCart",
        "itemsCheckedOut",
        "itemsClickedInList",
        "itemsClickedInPromotion",
        "itemsPurchased",
        "itemsViewed",
        "itemsViewedInList",
        "itemsViewedInPromotion",
        "newUsers",
        "organicGoogleSearchAveragePosition",
        "organicGoogleSearchClickThroughRate",
        "organicGoogleSearchClicks",
        "organicGoogleSearchImpressions",
        "promotionClicks",
        "promotionViews",
        "publisherAdClicks",
        "publisherAdImpressions",
        "purchaseRevenue",
        "purchaseToViewRate",
        "purchaserConversionRate",
        "refundAmount",
        "returnOnAdSpend",
        "screenPageViews",
        "screenPageViewsPerSession",
        "screenPageViewsPerUser",
        "scrolledUsers",
        "sessionConversionRate",
        "sessions",
        "sessionsPerUser",
        "shippingAmount",
        "taxAmount",
        "totalAdRevenue",
        "totalPurchasers",
        "totalRevenue",
        "totalUsers",
        "transactions",
        "transactionsPerPurchaser",
        "userConversionRate",
        "userEngagementDuration",
        "wauPerMau"
    ];
    /**
     * The available dimensions for validation.
     *
     * @var array
     */
    protected const AVAILABLE_DIMENSIONS = [
        'achievementId',
        'adFormat',
        'adSourceName',
        'adUnitName',
        'appVersion',
        'audienceName',
        'brandingInterest',
        'browser',
        'campaignId',
        'campaignName',
        'character',
        'city',
        'cityId',
        'cohort',
        'cohortNthDay',
        'cohortNthMonth',
        'cohortNthWeek',
        'contentGroup',
        'contentId',
        'contentType',
        'continent',
        'continentId',
        'country',
        'countryId',
        'currencyCode',
        'date',
        'dateHour',
        'dateHourMinute',
        'day',
        'dayOfWeek',
        'dayOfWeekName',
        'defaultChannelGroup',
        'deviceCategory',
        'deviceModel',
        'eventName',
        'fileExtension',
        'fileName',
        'firstSessionDate',
        'firstUserCampaignId',
        'firstUserCampaignName',
        'firstUserDefaultChannelGroup',
        'firstUserGoogleAdsAccountName',
        'firstUserGoogleAdsAdGroupId',
        'firstUserGoogleAdsAdGroupName',
        'firstUserGoogleAdsAdNetworkType',
        'firstUserGoogleAdsCampaignId',
        'firstUserGoogleAdsCampaignName',
        'firstUserGoogleAdsCampaignType',
        'firstUserGoogleAdsCreativeId',
        'firstUserGoogleAdsCustomerId',
        'firstUserGoogleAdsKeyword',
        'firstUserGoogleAdsQuery',
        'firstUserManualAdContent',
        'firstUserManualTerm',
        'firstUserMedium',
        'firstUserSource',
        'firstUserSourceMedium',
        'firstUserSourcePlatform',
        'fullPageUrl',
        'googleAdsAccountName',
        'googleAdsAdGroupId',
        'googleAdsAdGroupName',
        'googleAdsAdNetworkType',
        'googleAdsCampaignId',
        'googleAdsCampaignName',
        'googleAdsCampaignType',
        'googleAdsCreativeId',
        'googleAdsCustomerId',
        'googleAdsKeyword',
        'googleAdsQuery',
        'groupId',
        'hostName',
        'hour',
        'isConversionEvent',
        'isoWeek',
        'isoYear',
        'isoYearIsoWeek',
        'itemAffiliation',
        'itemBrand',
        'itemCategory',
        'itemCategory2',
        'itemCategory3',
        'itemCategory4',
        'itemCategory5',
        'itemId',
        'itemListId',
        'itemListName',
        'itemListPosition',
        'itemLocationID',
        'itemName',
        'itemPromotionCreativeName',
        'itemPromotionCreativeSlot',
        'itemPromotionId',
        'itemPromotionName',
        'itemVariant',
        'landingPage',
        'landingPagePlusQueryString',
        'language',
        'languageCode',
        'level',
        'linkClasses',
        'linkDomain',
        'linkId',
        'linkText',
        'linkUrl',
        'manualAdContent',
        'manualTerm',
        'medium',
        'method',
        'minute',
        'mobileDeviceBranding',
        'mobileDeviceMarketingName',
        'mobileDeviceModel',
        'month',
        'newVsReturning',
        'nthDay',
        'nthHour',
        'nthMinute',
        'nthMonth',
        'nthWeek',
        'nthYear',
        'operatingSystem',
        'operatingSystemVersion',
        'operatingSystemWithVersion',
        'orderCoupon',
        'outbound',
        'pageLocation',
        'pagePath',
        'pagePathPlusQueryString',
        'pageReferrer',
        'pageTitle',
        'percentScrolled',
        'platform',
        'platformDeviceCategory',
        'region',
        'screenResolution',
        'searchTerm',
        'sessionCampaignId',
        'sessionCampaignName',
        'sessionDefaultChannelGroup',
        'sessionGoogleAdsAccountName',
        'sessionGoogleAdsAdGroupId',
        'sessionGoogleAdsAdGroupName',
        'sessionGoogleAdsAdNetworkType',
        'sessionGoogleAdsCampaignId',
        'sessionGoogleAdsCampaignName',
        'sessionGoogleAdsCampaignType',
        'sessionGoogleAdsCreativeId',
        'sessionGoogleAdsCustomerId',
        'sessionGoogleAdsKeyword',
        'sessionGoogleAdsQuery',
        'sessionManualAdContent',
        'sessionManualTerm',
        'sessionMedium',
        'sessionSa360AdGroupName',
        'sessionSa360CampaignId',
        'sessionSa360CampaignName',
        'sessionSa360CreativeFormat',
        'sessionSa360EngineAccountId',
        'sessionSa360EngineAccountName',
        'sessionSa360EngineAccountType',
        'sessionSa360KeywordMatchType',
        'sessionSa360KeywordText',
        'sessionSource',
        'sessionSourceMedium',
        'sessionSourcePlatform',
        'shippingTier',
        'signedInWithUserId',
        'source',
        'sourceMedium',
        'sourcePlatform',
        'streamId',
        'streamName',
        'testDataFilterId',
        'testDataFilterName',
        'transactionId',
        'unifiedPagePathScreen',
        'unifiedPageScreen',
        'unifiedScreenClass',
        'unifiedScreenName',
        'userAgeBracket',
        'userGender',
        'videoProvider',
        'videoTitle',
        'videoUrl',
        'virtualCurrencyName',
        'visible',
        'week',
        'year',
        'yearMonth',
        'yearWeek'
    ];

    /**
     * Validate a batch of reports for a Google Analytics property.
     *
     * @param string|int $propertyId The ID of the property to fetch data for.
     * @param array[Report]    $reports    An array of reports, where each report is an associative array containing metrics, dimensions, etc.
     *
     * @throws InvalidArgumentException If there are more than 5 reports in the batch.
     * @return array|null An array of validation errors for each report that failed validation, or null if all reports are valid.
     */
    public static function validateBatchReports(string|int $propertyId, array $reports): ?array
    {
        // Check if there are more than 5 reports in the batch.
        if (count($reports) > 5) {
            throw new InvalidArgumentException('A batch can contain a maximum of 5 reports.');
        }

        // Initialize an empty array to collect errors.
        $batchErrors = [];

         /** Iterate through the reports. @var Report $report */
        foreach ($reports as $index => $report) {
            // Validate each report using the validateReport function.
            $reportErrors = self::validateReport($propertyId, $report->metrics, $report->dimensions, $report->limit, $report->orderBy, $report->offset, $report->keepEmptyRows);

            // If validation failed for this report, add it to the batchErrors array.
            if ($reportErrors) {
                $batchErrors["Report #$index"] = $reportErrors;
            }
        }

        // If there are batchErrors, return them. Otherwise, return null to indicate success.
        return !empty($batchErrors)
            ? $batchErrors
            : null;
    }

    /**
     * Validate input parameters.
     *
     * @param string|int $propertyId    The ID of the property to fetch data for.
     * @param string[]   $metrics       The metrics to include in the report.
     * @param string[]   $dimensions    The dimensions to include in the report.
     * @param int        $limit         The maximum number of results to return.
     * @param string[]   $orderBy       The order in which to return results.
     * @param int        $offset        The offset for pagination.
     * @param bool       $keepEmptyRows Whether to keep empty rows in the result.
     *
     * @return MessageBag|null
     */
    public static function validateReport(string|int $propertyId, array $metrics, array $dimensions = [], int $limit = 10, array $orderBy = [], int $offset = 0, bool $keepEmptyRows = false): MessageBag|null
    {
        // Create validator instance
        $validator = LaravelValidator::make([
            'property' => $propertyId,
            'metrics' => $metrics,
            'dimensions' => $dimensions,
            'limit' => $limit,
            'order_by' => $orderBy,
            'offset' => $offset,
            'keep_empty_rows' => $keepEmptyRows
        ], [
            'property' => 'required|string|size:9',
            'metrics' => 'required|array',
            'dimensions' => 'array',
            'limit' => 'required|integer|min:1',
            'order_by' => 'array',
            'offset' => 'required|integer|min:0',
            'keep_empty_rows' => 'required|boolean'
        ], [
            'valid_metrics' => __('The :attribute must be a valid metric or start with "customEvent:".'),
            'valid_dimensions' => __('The :attribute must be a valid dimension or start with "customEvent:".'),
        ]);

        // You can define a custom validation rule for 'metrics' and 'dimensions'
        LaravelValidator::extend('valid_metrics', function (string $attribute, array $value): bool {
            foreach ($value as $metric) {
                // Check if it's a valid metric or starts with 'customEvent:'
                if (!in_array($metric, self::AVAILABLE_METRICS) && !str_starts_with($metric, 'customEvent:')) {
                    return false;
                }
            }

            return true;
        });

        LaravelValidator::extend('valid_dimensions', function (string $attribute, array $value): bool {
            foreach ($value as $dimension) {
                // Check if it's a valid dimension or starts with 'customEvent:'
                if (!in_array($dimension, self::AVAILABLE_DIMENSIONS) && !str_starts_with($dimension, 'customEvent:')) {
                    return false;
                }
            }

            return true;
        });

        // Check if validation fails. If validation failed, return an array containing the validation errors.
        return $validator->fails()
            ? $validator->errors()
            : null;
    }
}