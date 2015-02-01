<?php

namespace Vilemka\Presenters;

use Vilemka\Components\PhotoSlider;
use Vilemka\Components\ReservationControl;
use Vilemka\Components\FooterControl;
use Vilemka\OccupationCalendar;

/**
 * TODO
 * kešovat calendáře
 */
class HomepagePresenter extends BasePresenter
{

	/** @persistent */
	public $monthMove = 0;


	/** @var \stdClass */
	protected $templateVars;

	/** @var OccupationCalendar */
	protected $occupationCalendar;

	/** @var ReservationControl */
	protected $reservationControl;

	/** @var FooterControl */
	protected $footerControl;


	/**
	 * @param array $templateVars
	 * @param OccupationCalendar $occupationCalendar
	 * @param ReservationControl $reservationControl
	 * @param FooterControl $footerControl
	 */
	public function __construct(array $templateVars, OccupationCalendar $occupationCalendar,
		ReservationControl $reservationControl, FooterControl $footerControl)
	{
		$this->occupationCalendar = $occupationCalendar;
		$this->reservationControl = $reservationControl;
		$this->footerControl = $footerControl;
		$this->templateVars = (object) $templateVars;
	}


	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->vars = $this->templateVars;
	}


	/**
	 * @param string $markWeek
	 * @throws \Nette\Application\BadRequestException
	 */
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


	/**
	 * @param int $loadMonth
	 */
	public function renderDefault($loadMonth = NULL)
	{
		$month = (int) date('n') + $this->monthMove;
		$year = (int) date('Y');

		// ajax request to month
		if ($this->isAjax() && $loadMonth !== NULL) {
			$calendar = $this->occupationCalendar->render($month + $loadMonth, $year);
			$response = new \Nette\Application\Responses\TextResponse($calendar);
			$this->sendResponse($response);
		}

		$this->template->calendar = $this->occupationCalendar;
		$this->template->month = $month;
		$this->template->year = $year;
	}


	/**
	 * @return PhotoSlider
	 */
	public function createComponentPhotoSlider()
	{
		return new PhotoSlider;
	}


	/**
	 * @return ReservationControl
	 */
	public function createComponentReservationControl()
	{
		return $this->reservationControl;
	}


	/**
	 * @return FooterControl
	 */
	public function createComponentFooterControl()
	{
		return $this->footerControl;
	}

}
