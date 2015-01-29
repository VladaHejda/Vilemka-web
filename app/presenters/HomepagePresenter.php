<?php

namespace Vilemka\Presenters;

use Nette\Forms\Form;
use Tracy\Debugger;
use Vilemka\Components\PhotoSlider;
use Vilemka\Components\ReservationForm;
use Vilemka\OccupationRepository;
use Vilemka\UserNotifier;
use Vilemka\AdminNotifier;
use Vilemka\OccupationCalendar;
use Vilemka\ValueObject\Order;

/**
 * TODO
 * kešovat calendáře
 */
class HomepagePresenter extends BasePresenter
{

	/** @persistent */
	public $monthMove = 0;


	/** @var OccupationRepository */
	protected $occupationRepository;

	/** @var OccupationCalendar */
	protected $occupationCalendar;

	/** @var ReservationForm */
	protected $reservationForm;

	/** @var UserNotifier */
	protected $userNotifier;

	/** @var AdminNotifier */
	protected $adminNotifier;


	/**
	 * @param OccupationRepository $occupationRepository
	 * @param OccupationCalendar $occupationCalendar
	 * @param ReservationForm $reservationForm
	 * @param UserNotifier $userNotifier
	 */
	public function __construct(OccupationRepository $occupationRepository, OccupationCalendar $occupationCalendar,
		ReservationForm $reservationForm, UserNotifier $userNotifier, AdminNotifier $adminNotifier)
	{
		$this->occupationRepository = $occupationRepository;
		$this->occupationCalendar = $occupationCalendar;
		$this->reservationForm = $reservationForm;
		$this->userNotifier = $userNotifier;
		$this->adminNotifier = $adminNotifier;
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
		if ($this->isAjax() && $loadMonth !== NULL) {
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
		$this->reservationForm->onSuccess[] = [$this, 'sendOrder'];
		return $this->reservationForm;
	}


	public function sendOrder(Form $form)
	{
		$values = $form->getValues();

		$order = new Order($values->from, $values->to, $values->name, $values->personCount, $values->email,
			$values->phone, $values->notice);

		$this->occupationRepository->insert($order);

		try {
			$this->adminNotifier->notify($order);
		} catch (\Nette\InvalidStateException $e) {
			Debugger::log($e, Debugger::ERROR);
		}

		if ($order->getEmail()) {
			try {
				$this->userNotifier->notify($order->getEmail(), $order);
			} catch (\Nette\InvalidStateException $e) {
				Debugger::log($e, Debugger::ERROR);
			}
		}

		// todo flash message by form (viz. http://forum.nette.org/cs/17720-vykresleni-casti-formulare-ve-vlastni-sablone)
		$this->redirect(303, 'this#reservation');
	}

}
