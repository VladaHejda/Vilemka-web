<?php

namespace Vilemka\Components;

class ContactForm extends \Nette\Application\UI\Form
{

	public function __construct()
	{
		parent::__construct();
		$this->createFields();
	}


	protected function createFields()
	{
		$this->addTextArea('message', 'Máte-li na nás nějaký dotaz, napište nám:')
			->addRule(self::MIN_LENGTH, 'Napište prosím alespoň %d znaků.', 20)
		;

		$this->addText('name', 'Vaše jméno:')
			->setRequired('Prosím, zadejte jméno.')
		;

		$this->addText('email', 'E-mailová adresa:')
			->setType('email')
			->addCondition(self::FILLED)
				->addRule(self::EMAIL, 'E-mailová adresa není správně.')
		;

		$this->addSubmit('send', 'Odeslat');
	}

}
