<?php

namespace Vilemka;

use Nette\Mail\Message;

class UserNotifier extends \Vilemka\Notifier
{

	public function notify(ValueObject\Order $order)
	{
		$this->setRecipient($order->getEmail());

		$mail = new Message;
		$mail->setSubject('Rezervace pobytu na Vilémka.cz');

		$body = 'Dobrý den,' . "\n"
			. 'přijali jsme Vaši objednávku rezervace ubytování na chatě Vilémka.' . "\n\n"
			. 'Detail objednávky:' . "\n\n"
			. sprintf('Vaše jméno: %s', $order->getName()) . "\n"
			. sprintf('Termín od soboty %s do soboty %s', $order->getFrom()->format('j. n.'), $order->getTo()->format('j. n. Y')) . "\n"
			. sprintf('Počet osob: %d', $order->getPersonsCount()) . "\n"
			. sprintf('E-mailová adreaa: %s', $order->getEmail()) . "\n"
			. ($order->getPhone() ? sprintf('Telefonní číslo: %s', $order->getPhone()) . "\n" : '')
			. ($order->getNotice() ? sprintf("Poznámka:\n%s", $order->getNotice()) . "\n" : '')
			. "\n"
			. 'Platba probíhá na místě. Prosíme, přijeďtě v den počátku rezervace od 14 hodin nebo déle. '
			. 'Děkujeme za pochopení.' . "\n\n"
			. 'S pozdravem a přáním hezkého dne' . "\n"
			. 'Hejda Vladislav' . "\n"
			. sprintf('Ubytování v jižních čechách - chata Vilémka (http://%s)', $_SERVER['HTTP_HOST']) . "\n"
			. sprintf('tel.: %s', '+420 739 352 926') . "\n"
			. sprintf('e-mail.: %s', $this->from[0]) . "\n"
		;

		$mail->setBody($body);

		$this->send($mail);
	}

}
