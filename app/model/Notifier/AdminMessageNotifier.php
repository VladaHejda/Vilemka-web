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
		$mail->setSubject('Vilémka.cz nová zpráva');

		$body = 'Nová zpráva:' . "\n\n"
			. sprintf('Jméno: %s', $name) . "\n"
			. sprintf('E-mailová adresa: %s', $email ? $email->getEmail() : '-') . "\n"
			. sprintf("Zpráva:\n%s", $message) . "\n\n"
			. sprintf('http://%s', $_SERVER['HTTP_HOST']) . "\n"
		;

		$mail->setBody($body);

		$this->send($mail);
	}

}
