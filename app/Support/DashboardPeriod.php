<?php

namespace App\Support;

use Carbon\Carbon;

class DashboardPeriod
{
    const TODAY = 'today';
    const YESTERDAY = 'yesterday';
    const LAST_7_DAYS = 'last7days';
    const LAST_90_DAYS = 'last90days';
    const WEEK = 'week';
    const THIS_MONTH = 'thismonth';
    const LAST_MONTH = 'lastmonth';

    /** @var string */
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param  string|null  $value
     * @return self
     */
    public static function fromRequest($value)
    {
        if ($value === null || $value === '') {
            return new self(self::LAST_90_DAYS);
        }

        if ($value === 'month') {
            return new self(self::THIS_MONTH);
        }

        $allowed = [
            self::TODAY,
            self::YESTERDAY,
            self::LAST_7_DAYS,
            self::LAST_90_DAYS,
            self::WEEK,
            self::THIS_MONTH,
            self::LAST_MONTH,
        ];

        if (! in_array($value, $allowed, true)) {
            return new self(self::LAST_90_DAYS);
        }

        return new self($value);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options()
    {
        return [
            ['value' => self::TODAY, 'label' => 'Today'],
            ['value' => self::YESTERDAY, 'label' => 'Yesterday'],
            ['value' => self::LAST_7_DAYS, 'label' => 'Last 7 days'],
            ['value' => self::LAST_90_DAYS, 'label' => 'Last 90 days'],
            ['value' => self::WEEK, 'label' => 'This week'],
            ['value' => self::THIS_MONTH, 'label' => 'This month'],
            ['value' => self::LAST_MONTH, 'label' => 'Last month'],
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function dateRange()
    {
        switch ($this->value) {
            case self::TODAY:
                return [Carbon::today()->format('Y-m-d'), Carbon::today()->format('Y-m-d')];
            case self::YESTERDAY:
                return [Carbon::yesterday()->format('Y-m-d'), Carbon::yesterday()->format('Y-m-d')];
            case self::LAST_7_DAYS:
                return [Carbon::today()->subDays(6)->format('Y-m-d'), Carbon::today()->format('Y-m-d')];
            case self::LAST_90_DAYS:
                return [Carbon::today()->subDays(89)->format('Y-m-d'), Carbon::today()->format('Y-m-d')];
            case self::WEEK:
                return [Carbon::now()->startOfWeek(Carbon::SUNDAY)->format('Y-m-d'), Carbon::today()->format('Y-m-d')];
            case self::THIS_MONTH:
                return [Carbon::now()->startOfMonth()->format('Y-m-d'), Carbon::today()->format('Y-m-d')];
            case self::LAST_MONTH:
                return [
                    Carbon::now()->subMonthNoOverflow()->startOfMonth()->format('Y-m-d'),
                    Carbon::now()->subMonthNoOverflow()->endOfMonth()->format('Y-m-d'),
                ];
            default:
                return [Carbon::today()->subDays(89)->format('Y-m-d'), Carbon::today()->format('Y-m-d')];
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function previousDateRange()
    {
        switch ($this->value) {
            case self::TODAY:
                return [Carbon::yesterday()->format('Y-m-d'), Carbon::yesterday()->format('Y-m-d')];
            case self::YESTERDAY:
                return [Carbon::today()->subDays(2)->format('Y-m-d'), Carbon::today()->subDays(2)->format('Y-m-d')];
            case self::LAST_7_DAYS:
                return [Carbon::today()->subDays(13)->format('Y-m-d'), Carbon::today()->subDays(7)->format('Y-m-d')];
            case self::LAST_90_DAYS:
                return [Carbon::today()->subDays(179)->format('Y-m-d'), Carbon::today()->subDays(90)->format('Y-m-d')];
            case self::WEEK:
                return [
                    Carbon::now()->subWeek()->startOfWeek(Carbon::SUNDAY)->format('Y-m-d'),
                    Carbon::now()->subWeek()->endOfWeek(Carbon::SATURDAY)->format('Y-m-d'),
                ];
            case self::THIS_MONTH:
                return [
                    Carbon::now()->subMonthNoOverflow()->startOfMonth()->format('Y-m-d'),
                    Carbon::now()->subMonthNoOverflow()->endOfMonth()->format('Y-m-d'),
                ];
            case self::LAST_MONTH:
                return [
                    Carbon::now()->subMonthsNoOverflow(2)->startOfMonth()->format('Y-m-d'),
                    Carbon::now()->subMonthsNoOverflow(2)->endOfMonth()->format('Y-m-d'),
                ];
            default:
                return [Carbon::today()->subDays(179)->format('Y-m-d'), Carbon::today()->subDays(90)->format('Y-m-d')];
        }
    }

    /**
     * @return string
     */
    public function label()
    {
        foreach (self::options() as $option) {
            if ($option['value'] === $this->value) {
                return $option['label'];
            }
        }

        return 'Last 90 days';
    }
}
