<?php

namespace Vilemka;

use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Vilemka\ValueObject\EmailAddress;

abstract class Notifier extends \Nette\Object
{

	/** @var array before message send listeners function(Message) */
	public $onBeforeSend = [];

	/** @var EmailAddress */
	protected $sender, $recipient;

	/** @var IMailer */
	protected $mailer;


	/**
	 * @param IMailer $mailer
	 * @param EmailAddress $sender
	 * @param EmailAddress $recipient
	 */
	public function __construct(IMailer $mailer, EmailAddress $sender, EmailAddress $recipient = null)
	{
		$this->sender = $sender;
		$this->recipient = $recipient;
		$this->mailer = $mailer;
	}


	public function setSender(EmailAddress $sender)
	{
		$this->sender = $sender;
	}


	public function setRecipient(EmailAddress $recipient)
	{
		$this->recipient = $recipient;
	}


	public function send(Message $mail)
	{
		if ($this->sender !== null) {
			$mail->setFrom(...$this->sender->getComplete());
		}
		if ($this->recipient !== null) {
			$mail->addTo(...$this->recipient->getComplete());
		}
		$this->onBeforeSend($mail);
		$this->mailer->send($mail);
	}

}
