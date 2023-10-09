<?php

namespace WezanEnterprises\LaravelAnalytics\src\Utility;

use Illuminate\Support\Facades\Validator as LaravelValidator;
use Illuminate\Validation\ValidationException;

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
     * Validate input parameters.
     *
     * @param string|int $propertyId    The ID of the property to fetch data for.
     * @param string[]   $metrics       The metrics to include in the report.
     * @param string[]   $dimensions    The dimensions to include in the report.
     * @param int        $maxResults    The maximum number of results to return.
     * @param string[]   $orderBy       The order in which to return results.
     * @param int        $offset        The offset for pagination.
     * @param bool       $keepEmptyRows Whether to keep empty rows in the result.
     *
     * @throws ValidationException
     *
     * @return void
     */
    public static function validate(string|int $propertyId, array $metrics, array $dimensions = [], int $maxResults = 10, array $orderBy = [], int $offset = 0, bool $keepEmptyRows = false): void
    {
        // Create validator instance
        $validator = LaravelValidator::make([
            'propertyId' => $propertyId,
            'metrics' => $metrics,
            'dimensions' => $dimensions,
            'maxResults' => $maxResults,
            'orderBy' => $orderBy,
            'offset' => $offset,
            'keepEmptyRows' => $keepEmptyRows
        ], [
            'propertyId' => 'required|string|size:9',
            'metrics' => 'required|array',
            'dimensions' => 'required|array',
            'maxResults' => 'required|integer|min:1',
            'orderBy' => 'required|array',
            'offset' => 'required|integer|min:0',
            'keepEmptyRows' => 'required|boolean'
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

        // Check if validation fails
        if ($validator->fails()) {
            // Validation failed, throw a ValidationException with the errors
            throw ValidationException::withMessages($validator->errors()->toArray());
        }
    }
}