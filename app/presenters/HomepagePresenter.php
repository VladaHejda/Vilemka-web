<?php

namespace Vilemka\Presenters;

use Nette\Forms\Form;
use Vilemka\Components\PhotoSlider;
use Vilemka\Components\ReservationForm;
use Vilemka\NewOrderMailer;
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


	/** @var OccupationCalendar */
	protected $occupationCalendar;

	/** @var ReservationForm */
	protected $reservationForm;

	/** @var NewOrderMailer */
	protected $mailer;


	/**
	 * @param OccupationCalendar $occupationCalendar
	 * @param ReservationForm $reservationForm
	 * @param NewOrderMailer $mailer
	 */
	public function __construct(OccupationCalendar $occupationCalendar, ReservationForm $reservationForm, NewOrderMailer $mailer)
	{
		$this->occupationCalendar = $occupationCalendar;
		$this->reservationForm = $reservationForm;
		$this->mailer = $mailer;
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

		$period = new \DatePeriod($values->from, $values->from->diff($values->to), $values->to);
		$order = new Order($period, $values->name, $values->personCount, $values->email,
			$values->phone, $values->notice);

		if ($this->debugEmail) {
			$this->mailer->onBeforeSend[] = function (\Nette\Mail\Message $mail) {
				$mail->addBcc($this->debugEmail); // todo pokud to bude notifikovat oba, je toto taky blbost
			};
		}
		$this->mailer->notify($order);

		// todo flash message by form (viz. http://forum.nette.org/cs/17720-vykresleni-casti-formulare-ve-vlastni-sablone)
		$this->redirect(303, 'this#reservation');
	}

}
