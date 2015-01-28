<?php

namespace Vilemka\Presenters;
use Vilemka\Components\PhotoSlider;
use Vilemka\Components\ReservationForm;
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

	/** @var ReservationForm */
	protected $reservationForm;


	/**
	 * @param OccupationCalendar $occupationCalendar
	 */
	public function __construct(OccupationCalendar $occupationCalendar, ReservationForm $reservationForm)
	{
		$this->occupationCalendar = $occupationCalendar;
		$this->reservationForm = $reservationForm;
	}


	public function actionDefault($markWeek = '')
	{
		try {
			$this->occupationCalendar->injectDataString($markWeek);
		} catch (\InvalidArgumentException $e) {
			throw new \Nette\Application\BadRequestException($e->getMessage());
		}
		$this->occupationCalendar->setLinkCreator(function($dataString) {
			return $this->link('this#obsazenost', ['markWeek' => $dataString]);
		});
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
		return new PhotoSlider;
	}


	public function createComponentReservationForm()
	{
		$this->reservationForm->action .= '#reservation';
		$this->reservationForm->onSuccess[] = function() {
			// todo flash message (viz. http://forum.nette.org/cs/17720-vykresleni-casti-formulare-ve-vlastni-sablone)
			$this->redirect(303, 'this#reservation');
		};
		return $this->reservationForm;
	}

}
