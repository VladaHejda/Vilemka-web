<?php

namespace Vilemka;

use Nette\Mail\Message;

class UserOrderNotifier extends UserNotifier
{

	/**
	 * @param ValueObject\Order $order
	 */
	public function notify(ValueObject\Order $order)
	{
		if ($order->getEmail() === null) {
			throw new \InvalidArgumentException('Cannot notify user with no e-mail.');
		}

		$this->setRecipient($order->getEmail());

		$mail = new Message;
		$mail->setSubject('Rezervace pobytu na Vilémka.cz');

		$body = 'Dobrý den,' . "\n"
			. 'přijali jsme Vaši objednávku rezervace ubytování na chatě Vilémka.' . "\n\n"
			. 'Detail objednávky:' . "\n\n"
			. sprintf('Vaše jméno: %s', $order->getName()) . "\n"
			. sprintf('Termín od soboty %s do soboty %s', $order->getFrom()->format('j. n.'), $order->getTo()->format('j. n. Y')) . "\n"
			. sprintf('Počet osob: %d', $order->getPersonsCount()) . "\n"
			. ($order->getEmail() ? sprintf('E-mailová adresa: %s', $order->getEmail()->getEmail()) . "\n" : '')
			. ($order->getPhone() ? sprintf('Telefonní číslo: %s', $order->getPhone()) . "\n" : '')
			. ($order->getNotice() ? sprintf("Poznámka:\n%s", $order->getNotice()) . "\n" : '')
			. "\n"
//			. 'Platba probíhá na místě. Prosíme, přijeďtě v den počátku rezervace nejdříve ve 14 hodin (nebo déle). '
//			. 'Děkujeme za pochopení.' . "\n\n" // todo to se týká až při potvrzení rezervace
			. 'Potvrzení rezervace Vám co nejdříve zašleme.' . "\n"
			. ($this->signature ? "\n{$this->signature}\n" : '')
		;

		$mail->setBody($body);

		$this->send($mail);
	}

}
