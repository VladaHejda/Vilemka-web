<?php

namespace Vilemka\Presenters;

use Nette\Forms\Form;
use Tracy\Debugger;
use Vilemka\Components\PhotoSlider;
use Vilemka\Components\ReservationForm;
use Vilemka\OccupationRepository;
use Vilemka\UserOrderNotifier;
use Vilemka\AdminOrderNotifier;
use Vilemka\OccupationCalendar;
use Vilemka\ValueObject\EmailAddress;
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

	/** @var UserOrderNotifier */
	protected $userOrderNotifier;

	/** @var AdminOrderNotifier */
	protected $adminOrderNotifier;

	/** @var string */
	protected $idNumber;


	/**
	 * @param string $idNumber
	 * @param OccupationRepository $occupationRepository
	 * @param OccupationCalendar $occupationCalendar
	 * @param ReservationForm $reservationForm
	 * @param UserOrderNotifier $userOrderNotifier
	 */
	public function __construct($idNumber, OccupationRepository $occupationRepository, OccupationCalendar $occupationCalendar,
		ReservationForm $reservationForm, UserOrderNotifier $userOrderNotifier, AdminOrderNotifier $adminOrderNotifier)
	{
		$this->idNumber = $idNumber;
		$this->occupationRepository = $occupationRepository;
		$this->occupationCalendar = $occupationCalendar;
		$this->reservationForm = $reservationForm;
		$this->userOrderNotifier = $userOrderNotifier;
		$this->adminOrderNotifier = $adminOrderNotifier;
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
	 * @return ReservationForm
	 */
	public function createComponentReservationForm()
	{
		$this->reservationForm->action .= '#reservation';
		$this->reservationForm->onSuccess[] = [$this, 'sendOrder'];
		return $this->reservationForm;
	}


	/**
	 * @param Form $form
	 */
	public function sendOrder(Form $form)
	{
		$values = $form->getValues();

		$order = new Order($values->from, $values->to, $values->name, $values->personCount,
			$values->email ? new EmailAddress($this->getHttpRequest(), $values->email, $values->name) : null,
			$values->phone, $values->notice);

		$this->occupationRepository->insert($order);

		try {
			$this->adminOrderNotifier->notify($order);
		} catch (\Nette\InvalidStateException $e) {
			Debugger::log($e, Debugger::ERROR);
		}

		if ($order->getEmail()) {
			try {
				$this->userOrderNotifier->notify($order, $this->idNumber);
			} catch (\Nette\InvalidStateException $e) {
				Debugger::log($e, Debugger::ERROR);
			}
		}

		// todo flash message by form (viz. http://forum.nette.org/cs/17720-vykresleni-casti-formulare-ve-vlastni-sablone)
		$this->redirect(303, 'this#reservation');
	}

}
