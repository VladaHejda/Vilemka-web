<?php

namespace Vilemka\Components;

use Nette\Forms\Form;
use Nette\Http\Request;
use Tracy\Debugger;
use Vilemka\ValueObject\EmailAddress;
use Vilemka\ValueObject\Order;
use Vilemka\OccupationRepository;
use Vilemka\UserOrderNotifier;
use Vilemka\AdminOrderNotifier;

class ReservationControl extends \Nette\Application\UI\Control
{

	/** @var ReservationForm */
	protected $form;

	/** @var string */
	protected $idNumber;

	/** @var OccupationRepository */
	protected $occupationRepository;

	/** @var UserOrderNotifier */
	protected $userOrderNotifier;

	/** @var AdminOrderNotifier */
	protected $adminOrderNotifier;

	protected $request;


	/**
	 * @param string $idNumber
	 * @param ReservationForm $form
	 * @param OccupationRepository $occupationRepository
	 * @param UserOrderNotifier $userOrderNotifier
	 * @param AdminOrderNotifier $adminOrderNotifier
	 * @param Request $request
	 */
	public function __construct($idNumber, ReservationForm $form, OccupationRepository $occupationRepository,
		UserOrderNotifier $userOrderNotifier, AdminOrderNotifier $adminOrderNotifier, Request $request)
	{
		parent::__construct();
		$this->idNumber = $idNumber;
		$this->occupationRepository = $occupationRepository;
		$this->userOrderNotifier = $userOrderNotifier;
		$this->adminOrderNotifier = $adminOrderNotifier;
		$this->form = $form;
		$this->request = $request;
	}


	public function createComponentReservationForm()
	{
		$this->form->onSuccess[] = [$this, 'sendOrder'];
		return $this->form;
	}


	public function render()
	{
		$this->template->setFile(__DIR__ . '/reservationControl.latte');
		$this->template->render();
	}


	/**
	 * @param Form $form
	 */
	public function sendOrder(Form $form)
	{
		$values = $form->getValues();

		$order = new Order($values->from, $values->to, $values->name, $values->personCount,
			$values->email ? new EmailAddress($this->request, $values->email, $values->name) : null,
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

		$this->flashMessage('Objednávka odeslána. Brzy se Vám ozveme. Děkujeme!');
		$this->redirect(303, 'this#rezervace');
	}

}
