<?php

namespace Vilemka;

use Nette\Database\Context;

/**
 * @todo cache
 */
class OccupationCalendar
{

	/** @var \Calendar */
	protected $calendar;

	/** @var Context */
	protected $database;


	public function __construct(\Calendar $calendar, Context $context)
	{
		$this->calendar = $calendar;
		$this->database = $context;

		$this->calendar
			->setDayOfWeekClasses([0 => 'weekend', 6 => 'weekend'])
			->setStartingDay(\Calendar::MONDAY)
			->setIncludeWeekNumbers(FALSE)
			->setDayPattern('<span>%d</span>')
			->setMonthPattern('%s <span>%y</span>');
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
	 * @return \DatePeriod[]
	 */
	protected function getBookedPeriods($month, $year)
	{
		// todo
		$bookings = [
			'2014-07-19',
			'2014-08-02',
			'2014-08-30',
		];
		$interval = 6;

		$periods = [];
		foreach ($bookings as $booking) {
			$booking = new \DateTime($booking);
			$periods[] = new \DatePeriod($booking, new \DateInterval('P1D'), $interval);
		}

		return $periods;
	}


	/**
	 * @param int $month
	 * @param int $year
	 * @param \DatePeriod[] $bookedPeriods
	 * @return \DateTime[]
	 */
	protected function calculateAvailableDays($month, $year, $bookedPeriods)
	{
		$today = new \DateTime;
		$currentYear = (int) $today->format('Y');
		$currentMonth = (int) $today->format('n');

		// not available days before today
		if ($year < $currentYear || ($year === $currentYear && $month < $currentMonth)) {
			return [];

		} elseif ($currentYear === $year && $month === $currentMonth) {
			$dayOfWeek = $today->format('w');
			$daysToSaturday = 6 - $dayOfWeek;
			$firstFocusDay = new \DateTime("+ $daysToSaturday days");
			$remainingDaysCount =  $firstFocusDay->format('t') - $firstFocusDay->format('j');

		} else {
			$firstFocusDay = new \DateTime("$year-$month-01");
			$remainingDaysCount = (int) $firstFocusDay->format('t') -1;
		}

		$exclude = [];
		foreach ($bookedPeriods as $bookedPeriod) {
			foreach ($bookedPeriod as $bookedDate) {
				$exclude[$bookedDate->format('Ymd')] = TRUE;
			}
		}

		$monthPeriod = new \DatePeriod($firstFocusDay, new \DateInterval('P1D'), $remainingDaysCount);

		$freeDays = [];
		foreach ($monthPeriod as $day) {
			if (!isset($exclude[$day->format('Ymd')])) {
				$freeDays[] = $day;
			}
		}

		return $freeDays;
	}


	/**
	 * @param \DatePeriod[] $periods
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
			$penetratedPeriod = new \DatePeriod($firstDay, new \DateInterval('P1D'), iterator_count($period) -1);
			$this->calendar->setExtraPeriodClass($penetratedPeriod, 'occupied-morning');
		}
	}


	protected function markAvailableDays(array $days)
	{
		foreach ($days as $day) {
			$this->calendar->setExtraDatePattern($day, '<a href="">%d</a>');
			$this->calendar->setExtraDateClass($day, 'week-' . $this->getSatSatWeekNumber($day));
		}
	}


	protected function getSatSatWeekNumber(\DateTime $date)
	{
		$date = clone $date;
		$date->add(new \DateInterval('P2D'));
		return (int) $date->format('W');
	}

}
