<?php

/**
 * Calendar renderer
 * @author 2012-2014 jsem@hejdav.cz Vladislav Hejda
 *
 * @todo if class set to FALSE, do not render any class
 * @todo %w week in day pattern
 *
 * In day/week pattern use:
 *   %d = Arabic number
 *   %e = Roman number
 *   %m = month number
 *   %y = year number
 *
 * In moth pattern use:
 *   %s = month name
 *   %d = month number
 *   %e = month number in Roman numeral
 *   %y = year number
 */
class Calendar
{

	const SUNDAY = 0,
		MONDAY = 1,
		TUESDAY = 2,
		WEDNESDAY = 3,
		THURSDAY = 4,
		FRIDAY = 5,
		SATURDAY = 6;

	/** @var int */
	protected $startingDay = self::SUNDAY;

	/** @var bool */
	protected $zerofillDays = FALSE,
		$zerofillWeeks = FALSE;

	/** @var string */
	protected $dayPattern = '%d',
		$weekPattern = '%d.',
		$outsideDayPattern = NULL;

	/** @var callable */
	protected $dayCellContentCallback;

	/** @var bool */
	protected $applyCallbackToOutsideDays = FALSE;

	/** @var bool */
	protected $includeWeekNumbers = TRUE;

	/** @var string */
	protected $weekNumbersHeading = '&nbsp;';

	/** @var array */
	protected $extraDatePattern = [];

	/** @var array */
	protected $extraDateClass = [];

	/** @var array */
	protected $dayClasses = [0 => 'sunday'];

	/** @var string */
	protected $monthPattern = '%s';

	/** @var array */
	protected $monthClasses = [];

	/** @var bool */
	protected $includeMonthHeadings = TRUE,
		$includeDayHeadings = TRUE;

	/** @var array */
	protected $monthHeadings = [
		'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August',
		'September', 'October', 'November', 'December'
	];

	/** @var array */
	protected $dayHeadings = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];

	/** @var string */
	protected $tableClass = 'calendar',
		$monthNameRowClass = 'month',
		$dayNamesRowClass = 'daynames',
		$weekNumberCellClass = 'week',
		$outsideDayCellClass = 'outday';

	/** @var bool */
	protected $addExtraClassesToOutsideDays = TRUE,
		$applyExtraPatternsToOutsideDays = FALSE;


	private $month, $year, $monthDaysCount, $lastMonthDaysCount, $columnCount, $shift, $daysBefore, $weekCount,
		$firstWeekNo, $startsWithLastWeek, $indent;


	public function __construct()
	{
		$this->setExtraDateClass(new \DateTime, 'today');
	}


	/**
	 * @param bool $value    whether pad day numbers with leading zero
	 * @return self
	 */
	public function setZerofillDays($value = TRUE)
	{
		$this->zerofillDays = (bool) $value;
		return $this;
	}


	/**
	 * @param bool $value    whether pad week numbers with leading zero
	 * @return self
	 */
	public function setZerofillWeeks($value = TRUE)
	{
		$this->zerofillWeeks = (bool) $value;
		return $this;
	}


	/**
	 * @param string $pattern    pattern of the day output
	 * @return self
	 */
	public function setDayPattern($pattern)
	{
		$this->dayPattern = (string) $pattern;
		return $this;
	}


	/**
	 * @param bool $value
	 * @return self
	 */
	public function setIncludeWeekNumbers($value = TRUE)
	{
		$this->includeWeekNumbers = (bool) $value;
		return $this;
	}


	/**
	 * @param string $heading
	 * @return self
	 */
	public function setWeekNumbersHeading($heading)
	{
		$this->weekNumbersHeading = (string) $heading;
		return $this;
	}


	/**
	 * @param string $pattern    pattern of the day output
	 * @return self
	 */
	public function setWeekPattern($pattern)
	{
		$this->weekPattern = (string) $pattern;
		return $this;
	}


	/**
	 * @param string $pattern    pattern for days that are off the month scope
	 *   NULL to use standard day pattern
	 * @return self
	 */
	public function setOutsideDayPattern($pattern)
	{
		$this->outsideDayPattern = $pattern === NULL ? NULL : (string) $pattern;
		return $this;
	}


	/**
	 * @param int $dayNumber    first day of week, 0 means Sunday, 6 Saturday
	 * @return self
	 */
	public function setStartingDay($dayNumber)
	{
		$this->startingDay = self::validateDayNumber($dayNumber);
		return $this;
	}


	/**
	 * @param int $dayNumber    0 means Sunday
	 * @param string $class
	 * @return self
	 */
	public function setDayOfWeekClass($dayNumber, $class)
	{
		$this->dayClasses[self::validateDayNumber($dayNumber)] = (string) $class;
		return $this;
	}


	/**
	 * @param array $dayClasses    indexed by day numbers
	 * @return self
	 */
	public function setDayOfWeekClasses(array $dayClasses)
	{
		$this->dayClasses = [];
		foreach ($dayClasses as $dayNumber => $class) {
			$this->setDayOfWeekClass($dayNumber, $class);
		}
		return $this;
	}


	/**
	 * @param string $pattern
	 * @return self
	 */
	public function setMonthPattern($pattern)
	{
		$this->monthPattern = (string) $pattern;
		return $this;
	}


	/**
	 * @param array $monthClasses    indexed by month numbers, 0 means January
	 * @return self
	 */
	public function setMonthClasses(array $monthClasses)
	{
		$this->monthClasses = [];
		foreach ($monthClasses as $monthNumber => $class) {
			$this->monthClasses[self::validateMonthNumber($monthNumber)] = (string) $class;
		}
		return $this;
	}


	/**
	 * @param bool $value
	 * @return self
	 */
	public function setIncludeMonthHeadings($value = TRUE)
	{
		$this->includeMonthHeadings = (bool) $value;
		return $this;
	}


	/**
	 * @param array $headings
	 * @return self
	 */
	public function setMonthHeadings(array $headings)
	{
		foreach ($headings as $monthNumber => $heading) {
			$this->monthHeadings[self::validateMonthNumber($monthNumber)] = (string) $heading;
		}
		return $this;
	}


	/**
	 * @param bool $value
	 * @return self
	 */
	public function setIncludeDayHeadings($value = TRUE)
	{
		$this->includeDayHeadings = (bool) $value;
		return $this;
	}


	/**
	 * @param array $headings
	 * @return self
	 */
	public function setDayHeadings(array $headings)
	{
		foreach ($headings as $dayNumber => $heading) {
			$this->dayHeadings[self::validateDayNumber($dayNumber)] = (string) $heading;
		}
		return $this;
	}


	/**
	 * @param string $class
	 * @return self
	 */
	public function setTableClass($class)
	{
		$this->tableClass = (string) $class;
		return $this;
	}


	/**
	 * @param string $class
	 * @return self
	 */
	public function setMonthNameRowClass($class)
	{
		$this->monthNameRowClass = (string) $class;
		return $this;
	}


	/**
	 * @param string $class
	 * @return self
	 */
	public function setDayNamesRowClass($class)
	{
		$this->dayNamesRowClass = (string) $class;
		return $this;
	}


	/**
	 * @param string $class
	 * @return self
	 */
	public function setWeekNumberCellClass($class)
	{
		$this->weekNumberCellClass = (string) $class;
		return $this;
	}


	/**
	 * @param string $class
	 * @return self
	 */
	public function setOutsideDayCellClass($class)
	{
		$this->outsideDayCellClass = (string) $class;
		return $this;
	}


	/**
	 * @param \DateTime $date
	 * @param string $pattern
	 * @return self
	 */
	public function setExtraDatePattern(\DateTime $date, $pattern)
	{
		$this->extraDatePattern[self::generateStamp($date)] = (string) $pattern;
		return $this;
	}


	/**
	 * @param \DatePeriod $period
	 * @param string $pattern
	 * @return self
	 */
	public function setExtraPeriodPattern(\DatePeriod $period, $pattern)
	{
		$pattern = (string) $pattern;
		foreach ($period as $date) {
			$this->setExtraDatePattern($date, $pattern);
		}
		return $this;
	}


	/**
	 * @param \DateTime $date
	 * @param string|array $class
	 * @return self
	 */
	public function setExtraDateClass(\DateTime $date, $class)
	{
		$stamp = self::generateStamp($date);
		if (!isset($this->extraDateClass[$stamp])) {
			$this->extraDateClass[$stamp] = [];
		}
		if (!is_array($class)) {
			$class = [$class];
		}
		foreach ($class as $c) {
			$this->extraDateClass[$stamp][(string) $c] = TRUE;
		}
		return $this;
	}


	/**
	 * @param \DatePeriod $period
	 * @param string|array $class
	 * @return self
	 */
	public function setExtraPeriodClass(\DatePeriod $period, $class)
	{
		foreach ($period as $date) {
			$this->setExtraDateClass($date, $class);
		}
		return $this;
	}


	/**
	 * @param bool $value
	 * @return self
	 */
	public function setAddExtraClassesToOutsideDays($value = TRUE)
	{
		$this->addExtraClassesToOutsideDays = (bool) $value;
		return $this;
	}


	/**
	 * @param bool $value
	 * @return self
	 */
	public function setApplyExtraPatternsToOutsideDays($value = TRUE)
	{
		$this->applyExtraPatternsToOutsideDays = (bool) $value;
		return $this;
	}


	/**
	 * In callback return NULL (or nothing) for fallback into pattern.
	 * @param callable $callback
	 * @return self
	 */
	public function setDayCellContentCallback(callable $callback)
	{
		$this->dayCellContentCallback = $callback;
		return $this;
	}


	/**
	 * @param bool $value
	 * @return self
	 */
	public function setApplyCallbackToOutsideDays($value = TRUE)
	{
		$this->applyCallbackToOutsideDays = $value;
		return $this;
	}


	/**
	 * @param int $month    1-12
	 * @param int $year     e.g. 2015
	 * @param int|FALSE $indentOffset
	 * @param string $indentString
	 * @return string
	 */
	public function render($month, $year, $indentOffset = 0, $indentString = "\t")
	{
		self::correctMonth($month, $year);

		$this->month = $month;
		$this->year = $year;

		$this->monthDaysCount = $this->calculateMonthDaysCount($month, $year);
		$this->lastMonthDaysCount = $this->calculateMonthDaysCount($month -1, $year);

		$this->columnCount = 7;
		if ($this->includeWeekNumbers){
			++$this->columnCount;
		}

		$this->shift = $this->calculateDaysShift();
		$this->daysBefore = $this->calculateDaysBefore();
		$this->weekCount = $this->calculateWeekCount(count($this->daysBefore));
		$this->startsWithLastWeek = $month === 1;

		if (count($this->daysBefore)) {
			$date = $this->createDate($this->daysBefore[0], $month -1, $year);
		} else {
			$date = $this->createDate(1, $month, $year);
		}
		$this->firstWeekNo = $this->calculateWeekNumber($date);

		$this->indent = function ($level) use ($indentOffset, $indentString) {
			if ($indentOffset === FALSE) {
				return '';
			}
			return "\n" . str_repeat($indentString, $level + $indentOffset);
		};

		$output = $this->build();
		if ($indentOffset !== FALSE) {
			$output .= "\n";
		}

		$this->clean();
		return $output;
	}


	/**
	 * @param int &$month
	 * @param int &$year
	 */
	public static function correctMonth(& $month, & $year)
	{
		if ($month > 12) {
			$year += floor($month /12);
			$month = $month %12;
		} elseif ($month < 1) {
			$month = abs($month);
			--$year;
			$year -= floor($month /12);
			$month = 12 - $month %12;
		}
	}


	/**
	 * @param int $number
	 * @return string
	 */
	public static function intToRoman($number)
	{
		static $romanNumerals = [
			'M' => 1000, 'CM' => 900,
			'D' => 500, 'CD' => 400,
			'C' => 100, 'XC' => 90,
			'L' => 50, 'XL' => 40,
			'X' => 10, 'IX' => 9,
			'V' => 5, 'IV' => 4,
			'I' => 1,
		];
		$result = '';
		foreach ($romanNumerals as $key => $val) {
			$result .= str_repeat($key, floor($number / $val));
			$number %= $val;
		}
		return $result;
	}


	/**
	 * @param int $number
	 * @param int $digitsCount
	 * @return string
	 */
	public static function zerofill($number, $digitsCount)
	{
		$length = strlen($number);
		$pad = '';
		if ($length < $digitsCount) {
			$pad = str_repeat('0', $digitsCount - $length);
		}
		return $pad . $number;
	}


	protected function createDate($day, $month, $year)
	{
		$this->correctMonth($month, $year);
		return new \DateTime("$year-$month-$day");
	}


	protected function calculateDaysShift()
	{
		// start day shift
		if ($this->startingDay > 0) {
			$shift = [];
			for ($i = 0; $i < 7; $i++) {
				$dayShift = $this->startingDay + $i;
				if ($dayShift > 6){
					$dayShift = $dayShift -7;
				}
				$shift[$i] = $dayShift;
			}
			return $shift;
		} else {
			return range(0,6);
		}
	}


	/**
	 * Days outside month scope, left side.
	 */
	protected function calculateDaysBefore()
	{
		$daysCount = $this->calculateFirstMonthDay() - $this->startingDay;
		if ($daysCount < 0) {
			$daysCount += 7;
		}
		$days = [];
		for ($i = $daysCount -1; $i >= 0; $i--) {
			$days[] = $this->lastMonthDaysCount -$i;
		}
		return $days;
	}


	protected function calculateWeekCount($daysBeforeCount)
	{
		return ceil(($this->monthDaysCount + $daysBeforeCount) / 7);
	}


	protected function calculateWeekNumber(\DateTime $date)
	{
		return (int) $date->format('W');
	}


	protected function calculateFirstMonthDay()
	{
		$date = $this->createDate(1, $this->month, $this->year);
		return $date->format('w');
	}


	protected function calculateMonthDaysCount($month, $year)
	{
		$this->correctMonth($month, $year);
		return cal_days_in_month(CAL_GREGORIAN, $month, $year);
	}


	protected function getDateClasses(\DateTime $date)
	{
		$stamp = self::generateStamp($date);
		if (isset($this->extraDateClass[$stamp])){
			return array_keys($this->extraDateClass[$stamp]);
		}
		return [];
	}


	protected function applyPattern($pattern, \DateTime $date, $useExtra = TRUE)
	{
		if ($useExtra) {
			$stamp = self::generateStamp($date);
			if (isset($this->extraDatePattern[$stamp])) {
				$pattern = $this->extraDatePattern[$stamp];
			}
		}
		return $this->replaceDelegates($pattern, $date->format($this->zerofillDays ? 'd' : 'j'));
	}


	protected function replaceDelegates($pattern, $number)
	{
		$search = ['%d', '%m', '%y'];
		$replace = [$number, $this->month, $this->year];

		if (strpos($pattern, '%e') !== FALSE) {
			$search[] = '%e';
			$replace[] = self::intToRoman($number);
		}

		return str_replace($search, $replace, $pattern);
	}


	/**
	 * @param \DateTime $date
	 * @return string
	 */
	protected static function generateStamp(\DateTime $date)
	{
		return $date->format('Ymd');
	}


	/**
	 * @param int $dayNumber
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	protected static function validateDayNumber($dayNumber)
	{
		$dayNumber = (int) $dayNumber;
		if ($dayNumber < 0 || $dayNumber > 6) {
			throw new \InvalidArgumentException("Day number must be an integer between 0 (Sun) and 6 (Sat). $dayNumber is not.");
		}
		return $dayNumber;
	}


	/**
	 * @param int $monthNumber
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	protected static function validateMonthNumber($monthNumber)
	{
		$monthNumber = (int) $monthNumber;
		if ($monthNumber < 0 || $monthNumber > 11) {
			throw new \InvalidArgumentException("Month index must be an integer between 0 (Jan) and 11 (Dec). $monthNumber is not.");
		}
		return $monthNumber;
	}


	/**
	 * @return string
	 */
	private function build()
	{
		$indent = $this->indent;

		$classes = [$this->tableClass];
		if (isset($this->monthClasses[$this->month -1])) {
			$classes[] = $this->monthClasses[$this->month -1];
		}
		$output = $indent(0) . '<table class="' . implode(' ', $classes) .'">';
		$tableHeadDumped = FALSE;

		// month name
		if ($this->includeMonthHeadings) {
			if (!$tableHeadDumped) {
				$output .= $indent(1) . '<thead>';
				$tableHeadDumped = TRUE;
			}
			$output .= $this->buildMonthHeading();
		}

		// day names
		if ($this->includeDayHeadings) {
			if (!$tableHeadDumped) {
				$output .= $indent(1) . '<thead>';
				$tableHeadDumped = TRUE;
			}
			$output .= $this->buildDayHeadings();
		}

		if ($tableHeadDumped) {
			$output .= $indent(1) . '</thead>';
		}

		$output .= $indent(1) . '<tbody>';
		$output .= $this->buildBody();
		$output .= $indent(1) . '</tbody>';
		$output .= $indent(0) . '</table>';

		return $output;
	}


	private function buildMonthHeading()
	{
		$indent = $this->indent;

		$heading = $indent(2) . '<tr class="' . $this->monthNameRowClass;
		$heading .= '">';

		$search = ['%s', '%d', '%y'];
		$replace = [
			$this->monthHeadings[$this->month -1],
			$this->month,
			$this->year
		];

		if (strpos($this->monthPattern, '%e') !== FALSE) {
			$search[] = '%e';
			$replace[] = self::intToRoman($this->month);
		}

		$text = str_replace($search, $replace, $this->monthPattern);
		$heading .= $indent(3) . '<td colspan="' . $this->columnCount . '">' . $text . '</td>';

		$heading .= $indent(2) . '</tr>';
		return $heading;
	}


	private function buildDayHeadings()
	{
		$indent = $this->indent;

		$headings = $indent(2) . '<tr class="' . $this->dayNamesRowClass . '">';

		//  free position over week number
		if ($this->includeWeekNumbers) {
			$headings .= $indent(3) . '<td class="' . $this->weekNumberCellClass . '">' . $this->weekNumbersHeading . '</td>';
		}

		// days
		for ($i = 0; $i < 7; $i++) {
			$headings .= $indent(3) . '<td';
			// day of week  class
			if (isset($this->dayClasses[$this->shift[$i]])) {
				$headings .= ' class="' . $this->dayClasses[$this->shift[$i]] . '"';
			}
			$headings .= '>' . $this->dayHeadings[$this->shift[$i]] . '</td>';
		}
		$headings .= $indent(2) . '</tr>';

		return $headings;
	}


	private function buildBody()
	{
		$indent = $this->indent;

		$daysBeforeDumped = FALSE;
		$day = $daysAfter = 1;

		if ($this->outsideDayPattern === NULL) {
			$outsideDayPattern = $this->dayPattern;
		} else {
			$outsideDayPattern = $this->outsideDayPattern;
		}

		$body = '';

		for ($rowNo = 0; $rowNo < $this->weekCount; $rowNo++) {
			$body .= $indent(2) . '<tr>';

			// week number
			if ($this->includeWeekNumbers) {
				$weekString = $this->firstWeekNo++;
				$weekString = $this->zerofillWeeks ? self::zerofill($weekString, 2) : $weekString;
				$body .= $indent(3) . '<td class="' . $this->weekNumberCellClass . '">'
					. $this->replaceDelegates($this->weekPattern, $weekString) . '</td>';
				if ($this->startsWithLastWeek) {
					$this->firstWeekNo = 1;
					$this->startsWithLastWeek = FALSE;
				}
			}

			// days off month scope, left side
			if (!$daysBeforeDumped) {
				foreach ($this->daysBefore as $i => $dayBefore) {

					$date = $this->createDate($dayBefore, $this->month -1, $this->year);
					if ($this->addExtraClassesToOutsideDays) {
						$classes = $this->getDateClasses($date);
					} else {
						$classes = [];
					}

					$classes[] = $this->outsideDayCellClass;
					if (isset($this->dayClasses[$this->shift[$i]])) {
						$classes[] = $this->dayClasses[$this->shift[$i]];
					}
					$content = NULL;
					if ($this->applyCallbackToOutsideDays && $this->dayCellContentCallback) {
						$content = call_user_func($this->dayCellContentCallback, $date);
					}
					if ($content === NULL) {
						$content = $this->applyPattern($outsideDayPattern, $date, $this->applyExtraPatternsToOutsideDays);
					}
					$body .= $indent(3) . '<td class="' . implode(' ', $classes).'">' . $content . '</td>';
				}
				$daysBeforeDumped = TRUE;
				$startingDay = count($this->daysBefore);
			} else {
				$startingDay = 0;
			}

			// row
			for ($columnNo = $startingDay; $columnNo < 7; $columnNo++) {

				// days off month scope, right side
				if ($day > $this->monthDaysCount) {
					$date = $this->createDate($daysAfter, $this->month +1, $this->year);
					if ($this->addExtraClassesToOutsideDays) {
						$classes = $this->getDateClasses($date);
					} else {
						$classes = [];
					}
					$classes[] = $this->outsideDayCellClass;
					if (isset($this->dayClasses[$this->shift[$columnNo]])) {
						$classes[] = $this->dayClasses[$this->shift[$columnNo]];
					}
					$content = NULL;
					if ($this->applyCallbackToOutsideDays && $this->dayCellContentCallback) {
						$content = call_user_func($this->dayCellContentCallback, $date);
					}
					if ($content === NULL) {
						$content = $this->applyPattern($outsideDayPattern, $date, $this->applyExtraPatternsToOutsideDays);
					}
					$body .= $indent(3) . '<td class="' . implode(' ', $classes).'">' . $content . '</td>';
					++$daysAfter;
					continue;
				}

				$date = $this->createDate($day, $this->month, $this->year);
				$classes = $this->getDateClasses($date);
				if (isset($this->dayClasses[$this->shift[$columnNo]])) {
					$classes[] = $this->dayClasses[$this->shift[$columnNo]];
				}
				$body .= $indent(3) . '<td';
				if (count($classes)) {
					$body .= ' class="' . implode(' ', $classes) . '"';
				}
				$content = NULL;
				if ($this->dayCellContentCallback) {
					$content = call_user_func($this->dayCellContentCallback, $date);
				}
				if ($content === NULL) {
					$content = $this->applyPattern($this->dayPattern, $date);
				}
				$body .= '>' . $content . '</td>';
				++$day;
			}

			$body .= $indent(2) . '</tr>';
		}

		return $body;
	}


	private function clean()
	{
		$this->month = $this->year = $this->monthDaysCount = $this->lastMonthDaysCount = $this->columnCount
			= $this->shift = $this->daysBefore = $weekCount = $this->firstWeekNo = $this->startsWithLastWeek
			= NULL;
	}

}
