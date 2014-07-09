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


	public function renderDefault()
	{
		$this->template->calendar = $this->occupationCalendar;
		$this->template->month = (int) date('n') + $this->monthMove;
		$this->template->year = (int) date('Y');
	}

}
