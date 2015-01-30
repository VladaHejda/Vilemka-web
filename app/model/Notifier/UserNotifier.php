<?php

namespace Vilemka;

use Nette\Mail\Message;

class UserOrderNotifier extends \Vilemka\Notifier
{

	/**
	 * @param ValueObject\Order $order
	 * @param string $idNumber
	 */
	public function notify(ValueObject\Order $order, $idNumber)
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
			. sprintf('E-mailová adresa: %s', $order->getEmail()->getEmail()) . "\n"
			. ($order->getPhone() ? sprintf('Telefonní číslo: %s', $order->getPhone()) . "\n" : '')
			. ($order->getNotice() ? sprintf("Poznámka:\n%s", $order->getNotice()) . "\n" : '')
			. "\n"
			. 'Platba probíhá na místě. Prosíme, přijeďtě v den počátku rezervace nejdříve ve 14 hodin (nebo déle). '
			. 'Děkujeme za pochopení.' . "\n\n"
			. 'S pozdravem a přáním hezkého dne' . "\n"
			. 'Hejda Vladislav' . "\n"
			. sprintf('IČO: %s', $idNumber) . "\n"
			. sprintf('Ubytování v jižních čechách - chata Vilémka (http://%s)', $_SERVER['HTTP_HOST']) . "\n"
			. sprintf('tel.: %s', '+420 739 352 926') . "\n"
			. sprintf('e-mail.: %s', $this->sender->getEmail()) . "\n"
		;

		$mail->setBody($body);

		$this->send($mail);
	}

}
