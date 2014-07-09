<?php

namespace Vilemka\Presenters;
use Vilemka\OccupationCalendar;

/**
 * TODO
 * correct scroll on refresh!
 * bacha na měsíc co má 6 tejdnů! vyleze z pozadí
 * kešovat calendáře
 * #top anchor jquery scroll se posunuje špatně
 */
class HomepagePresenter extends BasePresenter
{

	/** @persistent */
	public $monthMove = 0;


	/** @var OccupationCalendar */
	protected $occupationCalendar;


	/**
	 * @param OccupationCalendar $occupationCalendar
	 */
	public function __construct(OccupationCalendar $occupationCalendar)
	{
		$this->occupationCalendar = $occupationCalendar;
	}


	public function renderDefault($loadMonth = NULL)
	{
		$month = (int) date('n') + $this->monthMove;
		$year = (int) date('Y');

		// ajax request to month
		if ($loadMonth !== NULL) {
			$calendar = $this->occupationCalendar->render($month + $loadMonth, $year);
			$response = new \Nette\Application\Responses\TextResponse($calendar);
			$this->sendResponse($response);
		}

		$this->template->calendar = $this->occupationCalendar;
		$this->template->month = $month;
		$this->template->year = $year;
	}

}
