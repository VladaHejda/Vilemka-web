<?php

namespace Vilemka;

use Nette\Mail\Message;

class AdminOrderNotifier extends \Vilemka\Notifier
{

	public function notify(ValueObject\Order $order)
	{
		$this->setRecipient($order->getEmail());

		$mail = new Message;
		$mail->setSubject('Vilémka.cz rezervace');

		$body = 'Zájem o rezervaci:' . "\n\n"
			. sprintf('Jméno: %s', $order->getName()) . "\n"
			. sprintf('Termín: %s - %s', $order->getFrom()->format('j. n.'), $order->getTo()->format('j. n. Y')) . "\n"
			. sprintf('Počet osob: %d', $order->getPersonsCount()) . "\n"
			. sprintf('E-mailová adresa: %s', $order->getEmail()->getEmail()) . "\n"
			. sprintf('Telefonní číslo: %s', $order->getPhone() ?: '-') . "\n"
			. sprintf('Poznámka:%s', $order->getNotice() ? "\n{$order->getNotice()}" : ' -') . "\n\n"
			. sprintf('http://%s', $_SERVER['HTTP_HOST']) . "\n"
		;

		$mail->setBody($body);

		$this->send($mail);
	}

}
