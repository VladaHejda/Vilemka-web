<?php

namespace Vilemka\Presenters;

/**
 * TODO
 * correct scroll on refresh!
 * bacha na měsíc co má 6 tejdnů! vyleze z pozadí
 * kešovat calendáře
 * #top anchor jquery scroll se posunuje špatně
 */
class HomepagePresenter extends BasePresenter
{

	/** @var \Calendar */
	protected $calendar;


	public function __construct(\Calendar $calendar)
	{
		$this->calendar = $calendar;
	}


	public function startup()
	{
		parent::startup();

		$this->calendar
			->setDayOfWeekClasses([0 => 'weekend', 6 => 'weekend'])
			->setDayHeadings(['NE', 'PO', 'ÚT', 'ST', 'ČT', 'PÁ', 'SO'])
			->setMonthHeadings(['Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září',
				'Říjen', 'Listopad', 'Prosinec'])
			->setStartingDay(\Calendar::MONDAY)
			->setIncludeWeekNumbers(FALSE);

		$this->template->calendar = $this->calendar;
	}


	public function renderDefault()
	{
		$this->prepareCalendar();
	}


	protected function prepareCalendar()
	{
		$year = (int) date('Y');
		$month = (int) date('n');

		$this->calendar->setDayPattern('<span>%d</span>');

		$freeSaturdays = [];
		$daysToSaturday = 6 - date('w');
		$availablePeriods = new \DatePeriod(new \DateTime("+ $daysToSaturday days"), new \DateInterval('P7D'), 14);
		foreach ($availablePeriods as $saturday) {
			$freeSaturdays[$saturday->format('Y-m-d')] = TRUE;
		}

		$bookings = [
			'2014-07-19',
			'2014-08-02',
			'2014-08-30',
		];

		$interval = 6;

		// occupied
		foreach ($bookings as $booking) {
			unset($freeSaturdays[$booking]);

			$booking = new \DateTime($booking);
			$period = new \DatePeriod($booking, new \DateInterval('P1D'), $interval);
			$this->calendar
				->setExtraPeriodClass($period, 'occupied-evening');

			$booking->add(new \DateInterval('P1D'));
			$period = new \DatePeriod($booking, new \DateInterval('P1D'), $interval);
			$this->calendar
				->setExtraPeriodClass($period, 'occupied-morning');
		}

		// available
		foreach (array_keys($freeSaturdays) as $n => $saturday) {
			$period = new \DatePeriod(new \DateTime($saturday), new \DateInterval('P1D'), 6);
			$this->calendar
				->setExtraPeriodPattern($period, '<a href="" data-occupied="' . $n . '">%d</a>');
		}


		$this->template->month = $month;
		$this->template->year = $year;
	}
}
