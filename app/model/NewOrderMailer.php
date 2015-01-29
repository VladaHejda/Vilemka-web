<?php

namespace Vilemka;

use Nette\Mail\Message;

class NewOrderMailer extends \Vilemka\Mailer
{

	public function notify(ValueObject\Order $order)
	{
		if ($order->email) {
			$this->notifyClient($order);
		}
		$this->notifySeller($order);
	}


	protected function notifyClient(ValueObject\Order $order)
	{
		$mail = new Message;
		$mail->addTo($order->email, $order->name);
		$mail->setSubject('Rezervace pobytu na Vilémka.cz');

		list($from, $to) = iterator_to_array($order->period);

		$body = 'Dobrý den,' . "\n"
			. 'přijali jsme Vaši objednávku rezervace ubytování na chatě Vilémka.' . "\n\n"
			. 'Detail objednávky:' . "\n\n"
			. sprintf('Vaše jméno: %s', $order->name) . "\n"
			. sprintf('Termín od soboty %s do soboty %s', $from->format('j. n.'), $to->format('j. n. Y')) . "\n"
			. sprintf('Počet osob: %d', $order->personsCount) . "\n"
			. sprintf('E-mailová adreaa: %s', $order->email) . "\n"
			. ($order->phone ? sprintf('Telefonní číslo: %s', $order->phone) . "\n" : '')
			. ($order->notice ? sprintf("Poznámka:\n%s", $order->notice) . "\n" : '')
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
