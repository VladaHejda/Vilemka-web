<?php

namespace Vilemka\Presenters;
use Vilemka\OccupationCalendar;

/**
 * TODO
 * kešovat calendáře
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


	public function createComponentPhotoSlider()
	{
		return new \Vilemka\Components\PhotoSlider;
	}

}
