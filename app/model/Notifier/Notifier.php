<?php

namespace Vilemka;

use Nette\Http\Request;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Utils\Validators;

abstract class Notifier extends \Nette\Object
{

	/** @var array [ $email, $name ] */
	protected $from = [];

	/** @var IMailer */
	protected $mailer;

	/** @var array before message send listeners function(Message) */
	public $onBeforeSend = [];


	/**
	 * @param IMailer $mailer
	 * @param Request $request
	 * @param string $email
	 * @param string $fromName
	 */
	public function __construct(IMailer $mailer, Request $request, $email, $fromName = null)
	{
		$email = str_replace('<host>', $request->getUrl()->getHost(), $email);

		if (!Validators::isEmail($email)) {
			throw new \InvalidArgumentException(sprintf('%s is not valid e-mail.', $email));
		}

		$this->from[] = $email;
		if ($fromName !== null) {
			$this->from[] = $fromName;
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
