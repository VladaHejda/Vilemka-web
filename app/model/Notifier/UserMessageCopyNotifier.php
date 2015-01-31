<?php

namespace Vilemka;

use Nette\Mail\Message;
use Vilemka\ValueObject\EmailAddress;

class UserMessageCopyNotifier extends UserNotifier
{

	/**
	 * @param EmailAddress $recipient
	 * @param string $message
	 */
	public function notify(EmailAddress $recipient, $message)
	{
		$this->setRecipient($recipient);

		$mail = new Message;
		$mail->setSubject('Kopie zprávy z Vilémka.cz');

		$body = 'Dobrý den,' . "\n"
			. 'přijali jsme od Vás zprávu z Vilémka.cz' . "\n\n"
			. sprintf('Vaše jméno: %s', $recipient->getName()) . "\n"
			. sprintf('E-mailová adresa: %s', $recipient->getEmail()) . "\n"
			. sprintf("Kopie Vaší zprávy:\n%s", $message) . "\n\n"
			. 'Děkujeme! Brzy se Vám ozveme.' . "\n"
			. ($this->signature ? "\n{$this->signature}\n" : '')
		;

		$mail->setBody($body);

		$this->send($mail);
	}

}
