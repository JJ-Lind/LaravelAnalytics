<?php

namespace WezanEnterprises\LaravelAnalytics;

use DateTimeInterface;
use Google\Analytics\Data\V1beta\DateRange;
use WezanEnterprises\LaravelAnalytics\Exceptions\InvalidPeriodException;

/**
 * Class Period
 *
 * Represents a time period defined by a start date and an end date.
 *
 * @package WezanEnterprises\LaravelAnalytics
 */
class Period {

    /**
     * The start date of the period.
     *
     * @var DateTimeInterface
     */
    private DateTimeInterface $startDate;
    /**
     * The end date of the period.
     *
     * @var DateTimeInterface
     */
    private DateTimeInterface $endDate;

    /**
     * Create a new Period instance.
     *
     * @param DateTimeInterface $startDate The start date of the period.
     * @param DateTimeInterface $endDate   The end date of the period.
     *
     * @throws InvalidPeriodException Thrown if the start date is after the end date.
     */
    public function __construct(DateTimeInterface $startDate, DateTimeInterface $endDate)
    {
        $this->validate($startDate, $endDate);

        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Validate that the start date is before or equal to the end date.
     *
     * @param DateTimeInterface $startDate The start date to validate.
     * @param DateTimeInterface $endDate   The end date to validate.
     *
     * @throws InvalidPeriodException Thrown if the start date is after the end date.
     */
    private function validate(DateTimeInterface $startDate, DateTimeInterface $endDate)
    {
        if ($startDate > $endDate) {
            throw new InvalidPeriodException("Start date `{$startDate->format('Y-m-d')}` cannot be after end date `{$endDate->format('Y-m-d')}`.");
        }
    }

    /**
     * Get the date range for this period.
     *
     * @return DateRange The date range object for this period.
     */
    public function getDateRange(): DateRange
    {
        return new DateRange([
            'start_date' => $this->startDate->format('Y-m-d'),
            'end_date' => $this->endDate->format('Y-m-d'),
        ]);
    }
}