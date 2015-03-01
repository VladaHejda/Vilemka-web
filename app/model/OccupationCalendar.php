<?php

namespace Vilemka;

use DateTime;
use DatePeriod;

/**
 * @todo cache
 */
class OccupationCalendar extends \Nette\Object
{

	/** @var \Calendar */
	protected $calendar;

	/** @var OccupationRepository */
	protected $occupationRepository;

	/** @var callable */
	protected $linkCreator;


	public function __construct(\Calendar $calendar, OccupationRepository $occupationRepository)
	{
		$this->calendar = $calendar;
		$this->occupationRepository = $occupationRepository;

		$monthNumbers = range(0, 11);
		foreach ($monthNumbers as &$monthNumber) {
			$monthNumber = "month-$monthNumber";
		}

		$this->calendar
			->setDayOfWeekClasses([0 => 'weekend', 6 => 'weekend'])
			->setStartingDay(\Calendar::MONDAY)
			->setIncludeWeekNumbers(FALSE)
			->setDayPattern('<span>%d</span>')
			->setMonthPattern('%s <span>%y</span>')
			->setMonthClasses($monthNumbers);
	}


	/**
	 * @param callable $linkCreator (string $dataString)
	 * @return self
	 */
	public function setLinkCreator(callable $linkCreator)
	{
		$this->linkCreator = $linkCreator;
		return $this;
	}


	public function render($month, $year)
	{
		\Calendar::correctMonth($month, $year);

		$month = (int) $month;
		$year = (int) $year;

		$bookedPeriods = $this->getBookedPeriods($month, $year);
		$availableDays = $this->calculateAvailableDays($month, $year, $bookedPeriods);

		$this->markOccupiedDays($bookedPeriods);
		$this->markAvailableDays($availableDays);

		return $this->calendar->render($month, $year);
	}


	/**
	 * @param int $month
	 * @param int $year
	 * @return DatePeriod[]
	 */
	protected function getBookedPeriods($month, $year)
	{
		$since = new DateTime;
		$since->modify(sprintf('+%d days', -(int) $since->format('w') +5));
		return $this->occupationRepository->getBookedPeriods($month, $year, $since);
	}


	/**
	 * @param int $month
	 * @param int $year
	 * @param DatePeriod[] $bookedPeriods
	 * @return DateTime[]
	 */
	protected function calculateAvailableDays($month, $year, $bookedPeriods)
	{
		$today = new DateTime;
		$currentYear = (int) $today->format('Y');
		$currentMonth = (int) $today->format('n');

		// not available days before today
		if ($year < $currentYear || ($year === $currentYear && $month < $currentMonth)) {
			return [];

		} elseif ($currentYear === $year && $month === $currentMonth) {
			$dayOfWeek = $today->format('w');
			$daysToSaturday = 6 - $dayOfWeek;
			$firstFocusDay = new DateTime("+ $daysToSaturday days");
			$this->calendar->setExtraDateClass($firstFocusDay, 'first-focus-day');
			$monthDaysCount = $firstFocusDay->format('t');
			$remainingDaysCount =  $monthDaysCount - $firstFocusDay->format('j');

		} else {
			$firstFocusDay = new DateTime("$year-$month-01");
			$monthDaysCount = $firstFocusDay->format('t');
			$remainingDaysCount = $monthDaysCount -1;
			$daysOutsideBefore = $firstFocusDay->format('w');
			if (!$daysOutsideBefore) {
				$daysOutsideBefore = 7;
			}
			if ($daysOutsideBefore) {
				$firstFocusDay->sub(new \DateInterval('P' . $daysOutsideBefore . 'D'));
				$remainingDaysCount += $daysOutsideBefore;
			}
		}

		$lastDay = new DateTime("$year-$month-$monthDaysCount");
		$daysOutsideAfter = $lastDay->format('w');
		if ($daysOutsideAfter) {
			$daysOutsideAfter = 7 - $daysOutsideAfter;
		}

		$exclude = [];
		foreach ($bookedPeriods as $bookedPeriod) {
			foreach ($bookedPeriod as $bookedDate) {
				$exclude[$bookedDate->format('Ymd')] = TRUE;
			}
		}

		$monthPeriod = new DatePeriod($firstFocusDay, new \DateInterval('P1D'), $remainingDaysCount + $daysOutsideAfter);

		$freeDays = [];
		foreach ($monthPeriod as $day) {
			if (!isset($exclude[$day->format('Ymd')])) {
				$freeDays[] = $day;
			}
		}

		return $freeDays;
	}


	/**
	 * @param DatePeriod[] $periods
	 */
	protected function markOccupiedDays(array $periods)
	{
		foreach ($periods as $period) {
			$this->calendar->setExtraPeriodClass($period, 'occupied-evening');

			// get first day of period
			foreach ($period as $firstDay) {
				break;
			}

			$firstDay->add(new \DateInterval('P1D'));
			$penetratedPeriod = new DatePeriod($firstDay, new \DateInterval('P1D'), iterator_count($period) -1);
			$this->calendar->setExtraPeriodClass($penetratedPeriod, 'occupied-morning');
		}
	}


	protected function markAvailableDays(array $days)
	{
		$lastWeekNumber = $lastDay = NULL;
		foreach ($days as $day) {
			$classes = [];
			$weekNumber = $this->getSatSatWeekNumber($day);
			if ($this->linkCreator) {
				$pattern = '<a href="' . call_user_func($this->linkCreator, (int) $day->format('Y'), $weekNumber) . '">%d</a>';
				$this->calendar->setExtraDatePattern($day, $pattern);
			}
			$classes[] = 'week-' . $weekNumber;
			$classes[] = 'available';
			if ($weekNumber !== $lastWeekNumber) {
				$classes[] = 'first-week-day';
				if ($lastDay) {
					$lastDay->add(new \DateInterval('P1D'));
					$this->calendar->setExtraDateClass($lastDay, 'week-' . $lastWeekNumber);
				}
			}
			$lastWeekNumber = $weekNumber;
			$lastDay = $day;
			$this->calendar->setExtraDateClass($day, $classes);
		}

		if (isset($day)) {
			$day->add(new \DateInterval('P1D'));
			$weekNumber = $this->getSatSatWeekNumber($day);

			if ($weekNumber !== $lastWeekNumber) {
				$this->calendar->setExtraDateClass($day, 'week-' . $lastWeekNumber);
			}
		}
	}


	protected function getSatSatWeekNumber(DateTime $date)
	{
		$date = clone $date;
		$date->add(new \DateInterval('P2D'));
		return (int) $date->format('W');
	}

}
