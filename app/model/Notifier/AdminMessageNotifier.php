<?php

namespace Vilemka;

use Nette\Mail\Message;
use Vilemka\ValueObject\EmailAddress;

class AdminMessageNotifier extends \Vilemka\Notifier
{

	/**
	 * @param string $message
	 * @param string $name
	 * @param EmailAddress $email
	 */
	public function notify($message, $name, EmailAddress $email = null)
	{
		$mail = new Message;
		$mail->setSubject('');

		$body = ''
			. "\n"
		;

		$mail->setBody($body);

//		$this->send($mail);
	}

}
