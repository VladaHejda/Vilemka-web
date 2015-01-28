<?php

namespace Vilemka\Components;

use Nette\Forms\Controls\TextBase;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

class ReservationForm extends \Nette\Application\UI\Form
{

	/** @persistent */
	public $photoMove = 0;

	/** @var int */
	protected $displayedPhotosCount = 3;


	/**
	 * @param int $maxPersonsCapacity
	 */
	public function __construct($maxPersonsCapacity)
	{
		$today = new DateTime;
		$datePattern = '(0?[0-9]|[12][0-9]|3[01])\\s*\\.\\s*(0?[0-9]|1[0-2])\\s*\\.\\s*[2-9][0-9]{3}';
		$dateParsePattern = '([0-9]+)\\s*\\.\\s*([0-9]+)\\s*\\.\\s*([0-9]+)';
		$dateFormatHelp = 'den. měsíc. rok';
		$labelFrom = 'Od';
		$labelTo = 'Do';

		$this->addText('from', "$labelFrom:")
			->setAttribute('placeholder', $dateFormatHelp)
			->setRequired()

			->addRule(self::PATTERN, sprintf('Uveďte prosím datum "%s" ve formátu "%s".', $labelFrom, $dateFormatHelp), $datePattern)

			->addRule(function (TextBase $control) use (& $fromDate, $dateParsePattern) {
				list(, $day, $month, $year) = Strings::match($control->getValue(), "/$dateParsePattern/");
				if (checkdate($month, $day, $year)) {
					$fromDate = DateTime::from(mktime(0, 0, 0, $month, $day, $year));
					return true;
				}
				return false;
			}, 'Datum %value neexistuje...')

			->addRule(function () use (& $fromDate, $today) {
				return $fromDate > $today;
			}, 'Datum "od" je v minulosti. Nelze rezerovovat termín v minulosti :)')

			->addRule(function() use (& $fromDate) {
				return $fromDate->format('w') == 6;
			}, sprintf('Lze rezervovat pouze turnusy od soboty do soboty. %s není sobota.', '%value'))
		;

		$this->addText('to', "$labelTo:")
			->setAttribute('placeholder', $dateFormatHelp)
			->setRequired()

			->addRule(self::PATTERN, sprintf('Uveďte prosím datum "%s" ve formátu "%s".', $labelTo, $dateFormatHelp), $datePattern)

			->addRule(function (TextBase $control) use (& $toDate, $dateParsePattern) {
				list(, $day, $month, $year) = Strings::match($control->getValue(), "/$dateParsePattern/");
				if (checkdate($month, $day, $year)) {
					$toDate = DateTime::from(mktime(0, 0, 0, $month, $day, $year));
					return true;
				}
				return false;
			}, 'Datum %value neexistuje...')

			->addRule(function() use (& $toDate, & $fromDate) {
				return $toDate > $fromDate;
			}, sprintf('Datum "%s" musí následovat až po datu "%s".', $labelTo, $labelFrom))

			->addRule(function() use (& $toDate) {
				return $toDate->format('w') == 6;
			}, sprintf('Lze rezervovat pouze turnusy od soboty do soboty. %s není sobota.', '%value'))
		;

		$this->addText('personCount', 'Počet osob:')
			->setType('number')
			->setRequired()
			->addRule(self::INTEGER, $message = 'Prosím, zadejte počet osob od %d do %d.', [1, $maxPersonsCapacity])
			->addRule(self::RANGE, $message, [1, $maxPersonsCapacity])
			->setAttribute('placeholder', sprintf('1 - %d', $maxPersonsCapacity))
		;

		$this->addText('email', 'E-mailová adresa:')
			->setType('email')
			->setDefaultValue('@')
			->addCondition(self::FILLED)
				->addRule(self::EMAIL, 'E-mailová adresa není správně.')
		;

		$this->addText('phone', 'Telefonní číslo:')
			->setRequired()
			->addCondition(self::FILLED)
				->addRule(self::PATTERN, 'Telefonní číslo není správně.', "^(\\+\\s*[0-9]{1,3}[- ]?)?[-0-9 ]{1,18}$")
		;

		$this->addTextArea('notice', 'Poznámka:');

		$this->addSubmit('send', 'Zaslat objednávku');

		$this->onSuccess[] = [$this, 'sendOrder'];
	}


	public function sendOrder(self $form)
	{

	}

}
