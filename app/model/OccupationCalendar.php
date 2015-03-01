<?php

namespace Vilemka;

/**
 * @todo cache
 */
class OccupationCalendar extends \Nette\Object
{

	/** @var \Calendar */
	protected $calendar;

	/** @var OccupationRepository */
	protected $occupationRepository;

	/** @var array $markedData [ int $year => [ int $satSatWeek, ... ], ... ] */
	protected $markedData;

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
	 * @param string $dataString
	 * @return self
	 * @throws \InvalidArgumentException
	 * @throws \BadMethodCallException
	 */
	public function injectDataString($dataString)
	{
		if ($this->markedData !== NULL) {
			throw new \BadMethodCallException('Data string already injected.');
		}

		$this->markedData = [];
		$dataString = trim($dataString);
		if (!empty($dataString)) {
			$today = new \DateTime;
			$currentYear = (int) $today->format('Y');
			$currentWeek = $this->getSatSatWeekNumber($today);

			foreach (explode('|', $dataString) as $weekData) {
				$weekData = explode('/', $weekData);
				if (count($weekData) !== 2) {
					throw new \InvalidArgumentException("Malformed data string '$dataString'.");
				}
				list($year, $week) = $weekData;
				if (!ctype_digit($year) || !ctype_digit($week) || $year < 0 || $week < 0 || $week > 53) {
					throw new \InvalidArgumentException("Malformed data string '$dataString'.");
				}
				if ($year < $currentYear || ($year === $currentYear && $week <= $currentWeek)) {
					continue;
				}
				if (isset($this->markedData[$year]) && in_array($week, $this->markedData[$year])) {
					continue;
				}
				$this->markedData[$year][] = $week;
			}
		}

		return $this;
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
		$markedDays = $this->calculateMarkedDays($month, $year, $availableDays);

		$this->markOccupiedDays($bookedPeriods);
		$this->markAvailableDays($availableDays);
//		$this->markMarkedDays($markedDays);

		return $this->calendar->render($month, $year);
	}


	public function getMarkedPeriodsSinceToday()
	{
		$today = new \DateTime;
		$currentWeek = $this->getSatSatWeekNumber($today);
		$currentYear = (int) $today->format('Y');
		foreach ($this->markedData as $year => $weeks) {

		}
	}


	/**
	 * @param int $month
	 * @param int $year
	 * @return \DatePeriod[]
	 */
	protected function getBookedPeriods($month, $year)
	{
		return $this->occupationRepository->getBookedPeriods($month, $year);
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
			$monthDaysCount = $firstFocusDay->format('t');
			$remainingDaysCount =  $monthDaysCount - $firstFocusDay->format('j');

		} else {
			$firstFocusDay = new \DateTime("$year-$month-01");
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

		$lastDay = new \DateTime("$year-$month-$monthDaysCount");
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

		$monthPeriod = new \DatePeriod($firstFocusDay, new \DateInterval('P1D'), $remainingDaysCount + $daysOutsideAfter);

		$freeDays = [];
		foreach ($monthPeriod as $day) {
			if (!isset($exclude[$day->format('Ymd')])) {
				$freeDays[] = $day;
			}
		}

		return $freeDays;
	}


	/**
	 * @param int $month
	 * @param int $year
	 * @param \DateTime[] $availableDays
	 * @return \DateTime[]
	 */
	protected function calculateMarkedDays($month, $year, $availableDays)
	{

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
		$lastWeekNumber = $lastDay = NULL;
		foreach ($days as $day) {
			$weekNumber = $this->getSatSatWeekNumber($day);
			if ($this->linkCreator) {
				$dataString = $this->getSerializedDataString((int) $day->format('Y'), $weekNumber);
				$pattern = '<a href="' . call_user_func($this->linkCreator, $dataString) . '">%d</a>';
				$this->calendar->setExtraDatePattern($day, $pattern);
			}
			$classes = [
				'week-' . $weekNumber,
				'available',
			];
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


	protected function getSatSatWeekNumber(\DateTime $date)
	{
		$date = clone $date;
		$date->add(new \DateInterval('P2D'));
		return (int) $date->format('W');
	}


	protected function getSerializedDataString($addYear, $addWeek)
	{
		$markedData = $this->markedData;
		if (isset($markedData[$addYear])) {
			$index = array_search($addWeek, $markedData[$addYear]);

			// mark
			if ($index === FALSE) {
				$markedData[$addYear][] = $addWeek;
			// unmark
			} else {
				unset($markedData[$addYear][$index]);
			}
		} else {
			$markedData[$addYear] = [$addWeek];
		}

		foreach ($markedData as $year => & $weeks) {
			foreach ($weeks as & $week) {
				$week = "$year/$week";
			}
			$weeks = implode('|', $weeks);
		}
		return implode('|', $markedData);
	}

}
