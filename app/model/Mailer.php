<?php

namespace Vilemka;

use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Utils\Validators;

abstract class Mailer extends \Nette\Object
{

	/** @var array [ $email, $name ] */
	protected $from = [];

	/** @var IMailer */
	protected $mailer;

	/** @var array before message send listeners function(Message) */
	public $onBeforeSend = [];


	/**
	 * @param IMailer $mailer
	 * @param string $email
	 * @param string $name
	 */
	public function __construct(IMailer $mailer, $email, $name = null)
	{
		str_replace('<host>', $_SERVER['HTTP_HOST'], $email);

		if (!Validators::isEmail($email)) {
			throw new \InvalidArgumentException(sprintf('%s is not valid e-mail.', $email));
		}

		$this->from[] = $email;
		if ($name !== null) {
			$this->from[] = $name;
		}

		$this->mailer = $mailer;
	}


	public function send(Message $mail)
	{
		$mail->setFrom(...$this->from);
		$this->onBeforeSend($mail);
		$this->mailer->send($mail);
	}

}
