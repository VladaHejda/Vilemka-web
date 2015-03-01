<?php

namespace Vilemka\Presenters;

use Nette\Application\BadRequestException;
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

	/** @var FooterControl */
	protected $photoSlider;


	/**
	 * @param array $templateVars
	 * @param OccupationCalendar $occupationCalendar
	 * @param ReservationControl $reservationControl
	 * @param FooterControl $footerControl
	 * @param PhotoSlider $photoSlider
	 */
	public function __construct(array $templateVars, OccupationCalendar $occupationCalendar,
		ReservationControl $reservationControl, FooterControl $footerControl, PhotoSlider $photoSlider)
	{
		$this->occupationCalendar = $occupationCalendar;
		$this->reservationControl = $reservationControl;
		$this->footerControl = $footerControl;
		$this->photoSlider = $photoSlider;
		$this->templateVars = (object) $templateVars;
	}


	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->vars = $this->templateVars;
	}


	/**
	 * @param string $markWeek
	 * @throws BadRequestException
	 */
	public function actionDefault($markWeek = '')
	{
		$markWeek = trim($markWeek);
		if (!empty($markWeek)) {
			@list($year, $week) = explode('/', $markWeek);
			if (!ctype_digit($year) || !ctype_digit($week) || $year < 0 || $week < 0 || $week > 53) {
				throw new BadRequestException(sprintf('Malformed week mark "%s".', $markWeek));
			}
			$this->reservationControl->setSelectedWeek($year, $week);
		}

		$this->occupationCalendar->setLinkCreator(function($year, $week) {
			return $this->link('this#rezervace', ['markWeek' => sprintf('%d/%d', $year, $week)]);
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
		return $this->photoSlider;
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
