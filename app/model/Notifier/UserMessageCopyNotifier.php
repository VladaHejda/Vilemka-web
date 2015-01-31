<?php

namespace Vilemka;

use Nette\Mail\Message;
use Vilemka\ValueObject\EmailAddress;

class UserMessageCopyNotifier extends \Vilemka\Notifier
{

	/**
	 * @param EmailAddress $recipient
	 * @param string $message
	 */
	public function notify(EmailAddress $recipient, $message)
	{
		$this->setRecipient($recipient);

		$mail = new Message;
		$mail->setSubject('');

		$body = ''
			. "\n"
		;

		$mail->setBody($body);

//		$this->send($mail);
	}

}
